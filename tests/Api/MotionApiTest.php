<?php

namespace App\Tests\Api;

use App\Tests\DatabaseTestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;

final class MotionApiTest extends DatabaseTestCase
{
    private const BVH = <<<BVH
        HIERARCHY
        ROOT Hips
        {
          OFFSET 0 0 0
          CHANNELS 6 Xposition Yposition Zposition Zrotation Xrotation Yrotation
          End Site
          {
            OFFSET 0 10 0
          }
        }
        MOTION
        Frames: 120
        Frame Time: 0.0333333
        BVH;

    public function testKeyframeMotionIsServedAsJson(): void
    {
        $this->client->request('GET', '/api/techniques/kibisu-gaeshi/motion');
        self::assertResponseIsSuccessful();
        self::assertResponseHeaderSame('Content-Type', 'application/json');

        $data = json_decode((string) file_get_contents($this->client->getResponse()->getFile()->getPathname()), true);
        self::assertSame('keyframes', $data['format']);
        self::assertNotEmpty($data['tracks']['tori']);
    }

    public function testTechniqueWithoutMotionGives404(): void
    {
        $this->getJson('/api/techniques/o-soto-gari/motion', 404);
    }

    public function testUploadNeedsTheToken(): void
    {
        $this->client->request('POST', '/api/techniques/o-soto-gari/motion', [], ['file' => $this->bvh()]);
        self::assertResponseStatusCodeSame(403);

        $this->client->request('POST', '/api/techniques/o-soto-gari/motion', [], ['file' => $this->bvh()], ['HTTP_AUTHORIZATION' => 'Bearer wrong']);
        self::assertResponseStatusCodeSame(403);
    }

    public function testUploadingABvhFileAddsAMotion(): void
    {
        $phases = json_encode([['t' => 0, 'name' => 'Kuzushi'], ['t' => 1.5, 'name' => 'Kake']]);
        $this->client->request('POST', '/api/techniques/o-soto-gari/motion', ['source' => 'Rokoko test', 'phases' => $phases],
            ['file' => $this->bvh()], ['HTTP_AUTHORIZATION' => 'Bearer test-token']);
        self::assertResponseStatusCodeSame(201);

        $t = $this->getJson('/api/techniques/o-soto-gari');
        self::assertSame('bvh', $t['motion']['format']);
        self::assertSame('Rokoko test', $t['motion']['source']);
        self::assertEqualsWithDelta(4.0, $t['motion']['duration'], 0.01);
        self::assertSame(['Kuzushi', 'Kake'], array_column($t['motion']['phases'], 'name'));
        self::assertSame(2, $this->getJson('/api/techniques?motion=1')['count']);

        $this->client->request('GET', '/api/techniques/o-soto-gari/motion');
        self::assertResponseIsSuccessful();
        self::assertStringStartsWith('text/plain', (string) $this->client->getResponse()->headers->get('Content-Type'));
    }

    public function testInvalidFilesAreRejectedWithAReason(): void
    {
        $fake = $this->file('not-really.bvh', "hello\n");
        $this->client->request('POST', '/api/techniques/o-soto-gari/motion', [], ['file' => $fake], ['HTTP_AUTHORIZATION' => 'Bearer test-token']);
        self::assertResponseStatusCodeSame(422);
        self::assertStringContainsString('HIERARCHY', (string) $this->client->getResponse()->getContent());

        $wrongType = $this->file('throw.mp4', 'x');
        $this->client->request('POST', '/api/techniques/o-soto-gari/motion', [], ['file' => $wrongType], ['HTTP_AUTHORIZATION' => 'Bearer test-token']);
        self::assertResponseStatusCodeSame(422);

        $badKeys = $this->file('k.json', json_encode(['format' => 'keyframes', 'duration' => 3, 'tracks' => ['tori' => [[0, ['bend' => 'lots']]], 'uke' => [[0, []]]]]));
        $this->client->request('POST', '/api/techniques/o-soto-gari/motion', [], ['file' => $badKeys], ['HTTP_AUTHORIZATION' => 'Bearer test-token']);
        self::assertResponseStatusCodeSame(422);
        self::assertStringContainsString('non-numeric', (string) $this->client->getResponse()->getContent());
    }

    public function testDeletingAMotion(): void
    {
        $this->client->request('DELETE', '/api/techniques/kibisu-gaeshi/motion', [], [], ['HTTP_AUTHORIZATION' => 'Bearer test-token']);
        self::assertResponseStatusCodeSame(204);
        self::assertNull($this->getJson('/api/techniques/kibisu-gaeshi')['motion']);
    }

    private function bvh(): UploadedFile
    {
        return $this->file('o-soto-gari.bvh', self::BVH);
    }

    private function file(string $name, string $contents): UploadedFile
    {
        $path = tempnam(sys_get_temp_dir(), 'motion');
        file_put_contents($path, $contents);

        return new UploadedFile($path, $name, null, null, true);
    }
}
