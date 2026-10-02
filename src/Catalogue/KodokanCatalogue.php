<?php

namespace App\Catalogue;

use App\Entity\Family;

/**
 * The 100 techniques recognised by the Kodokan: 68 nage-waza and 32 katame-waza.
 * Order within each category follows the Kodokan listing.
 */
final class KodokanCatalogue
{
    /**
     * @return list<array{slug: string, name: string, kanji: string, english: string, family: Family, techniques: list<array{name: string, kanji: string, english: string, gokyo: ?int, prohibited: bool, notes: ?string}>}>
     */
    public static function categories(): array
    {
        return [
            ['slug' => 'te', 'name' => 'Te-waza', 'kanji' => '手技', 'english' => 'Hand techniques', 'family' => Family::Nage, 'techniques' => [
                ['name' => 'Seoi-nage', 'kanji' => '背負投', 'english' => 'Shoulder throw', 'gokyo' => 1, 'prohibited' => false, 'notes' => null],
                ['name' => 'Ippon-seoi-nage', 'kanji' => '一本背負投', 'english' => 'One-arm shoulder throw', 'gokyo' => null, 'prohibited' => false, 'notes' => null],
                ['name' => 'Seoi-otoshi', 'kanji' => '背負落', 'english' => 'Shoulder drop', 'gokyo' => null, 'prohibited' => false, 'notes' => null],
                ['name' => 'Tai-otoshi', 'kanji' => '体落', 'english' => 'Body drop', 'gokyo' => 2, 'prohibited' => false, 'notes' => null],
                ['name' => 'Kata-guruma', 'kanji' => '肩車', 'english' => 'Shoulder wheel', 'gokyo' => 3, 'prohibited' => false, 'notes' => 'Tori’s arm takes uke’s thigh, which counts as a leg grab. IJF competition rules restrict leg grabs, so check the current rulebook before using it in shiai.'],
                ['name' => 'Sukui-nage', 'kanji' => '掬投', 'english' => 'Scooping throw', 'gokyo' => 4, 'prohibited' => false, 'notes' => 'Tori’s arm scoops behind uke’s legs, which counts as a leg grab. IJF competition rules restrict leg grabs, so check the current rulebook before using it in shiai.'],
                ['name' => 'Obi-otoshi', 'kanji' => '帯落', 'english' => 'Belt drop', 'gokyo' => null, 'prohibited' => false, 'notes' => 'Tori’s arm takes uke behind the knees, which counts as a leg grab. IJF competition rules restrict leg grabs, so check the current rulebook before using it in shiai.'],
                ['name' => 'Uki-otoshi', 'kanji' => '浮落', 'english' => 'Floating drop', 'gokyo' => 4, 'prohibited' => false, 'notes' => null],
                ['name' => 'Sumi-otoshi', 'kanji' => '隅落', 'english' => 'Corner drop', 'gokyo' => 5, 'prohibited' => false, 'notes' => null],
                ['name' => 'Yama-arashi', 'kanji' => '山嵐', 'english' => 'Mountain storm', 'gokyo' => null, 'prohibited' => false, 'notes' => null],
                ['name' => 'Obi-tori-gaeshi', 'kanji' => '帯取返', 'english' => 'Belt-grab reversal', 'gokyo' => null, 'prohibited' => false, 'notes' => null],
                ['name' => 'Morote-gari', 'kanji' => '双手刈', 'english' => 'Two-hand reap', 'gokyo' => null, 'prohibited' => false, 'notes' => 'A direct leg grab. IJF competition rules restrict leg grabs, so check the current rulebook before using it in shiai.'],
                ['name' => 'Kuchiki-taoshi', 'kanji' => '朽木倒', 'english' => 'Dead-tree drop', 'gokyo' => null, 'prohibited' => false, 'notes' => 'A direct leg grab. IJF competition rules restrict leg grabs, so check the current rulebook before using it in shiai.'],
                ['name' => 'Kibisu-gaeshi', 'kanji' => '踵返', 'english' => 'Heel trip reversal', 'gokyo' => null, 'prohibited' => false, 'notes' => 'A direct leg grab. IJF competition rules restrict leg grabs, so check the current rulebook before using it in shiai.'],
                ['name' => 'Uchi-mata-sukashi', 'kanji' => '内股すかし', 'english' => 'Inner-thigh void throw', 'gokyo' => null, 'prohibited' => false, 'notes' => null],
                ['name' => 'Ko-uchi-gaeshi', 'kanji' => '小内返', 'english' => 'Minor inner reap counter', 'gokyo' => null, 'prohibited' => false, 'notes' => null],
            ]],
            ['slug' => 'koshi', 'name' => 'Koshi-waza', 'kanji' => '腰技', 'english' => 'Hip techniques', 'family' => Family::Nage, 'techniques' => [
                ['name' => 'Uki-goshi', 'kanji' => '浮腰', 'english' => 'Floating hip', 'gokyo' => 1, 'prohibited' => false, 'notes' => null],
                ['name' => 'O-goshi', 'kanji' => '大腰', 'english' => 'Major hip throw', 'gokyo' => 1, 'prohibited' => false, 'notes' => null],
                ['name' => 'Koshi-guruma', 'kanji' => '腰車', 'english' => 'Hip wheel', 'gokyo' => 2, 'prohibited' => false, 'notes' => null],
                ['name' => 'Tsurikomi-goshi', 'kanji' => '釣込腰', 'english' => 'Lift-pull hip throw', 'gokyo' => 2, 'prohibited' => false, 'notes' => null],
                ['name' => 'Sode-tsurikomi-goshi', 'kanji' => '袖釣込腰', 'english' => 'Sleeve lift-pull hip throw', 'gokyo' => null, 'prohibited' => false, 'notes' => null],
                ['name' => 'Harai-goshi', 'kanji' => '払腰', 'english' => 'Sweeping hip throw', 'gokyo' => 2, 'prohibited' => false, 'notes' => null],
                ['name' => 'Tsuri-goshi', 'kanji' => '釣腰', 'english' => 'Lifting hip throw', 'gokyo' => 3, 'prohibited' => false, 'notes' => null],
                ['name' => 'Hane-goshi', 'kanji' => '跳腰', 'english' => 'Spring hip throw', 'gokyo' => 3, 'prohibited' => false, 'notes' => null],
                ['name' => 'Utsuri-goshi', 'kanji' => '移腰', 'english' => 'Changing hip throw', 'gokyo' => 4, 'prohibited' => false, 'notes' => null],
                ['name' => 'Ushiro-goshi', 'kanji' => '後腰', 'english' => 'Rear hip throw', 'gokyo' => 5, 'prohibited' => false, 'notes' => null],
            ]],
            ['slug' => 'ashi', 'name' => 'Ashi-waza', 'kanji' => '足技', 'english' => 'Foot and leg techniques', 'family' => Family::Nage, 'techniques' => [
                ['name' => 'De-ashi-harai', 'kanji' => '出足払', 'english' => 'Advanced foot sweep', 'gokyo' => 1, 'prohibited' => false, 'notes' => null],
                ['name' => 'Hiza-guruma', 'kanji' => '膝車', 'english' => 'Knee wheel', 'gokyo' => 1, 'prohibited' => false, 'notes' => null],
                ['name' => 'Sasae-tsurikomi-ashi', 'kanji' => '支釣込足', 'english' => 'Propping lift-pull foot throw', 'gokyo' => 1, 'prohibited' => false, 'notes' => null],
                ['name' => 'O-soto-gari', 'kanji' => '大外刈', 'english' => 'Major outer reap', 'gokyo' => 1, 'prohibited' => false, 'notes' => null],
                ['name' => 'O-uchi-gari', 'kanji' => '大内刈', 'english' => 'Major inner reap', 'gokyo' => 1, 'prohibited' => false, 'notes' => null],
                ['name' => 'Ko-soto-gari', 'kanji' => '小外刈', 'english' => 'Minor outer reap', 'gokyo' => 2, 'prohibited' => false, 'notes' => null],
                ['name' => 'Ko-uchi-gari', 'kanji' => '小内刈', 'english' => 'Minor inner reap', 'gokyo' => 2, 'prohibited' => false, 'notes' => null],
                ['name' => 'Okuri-ashi-harai', 'kanji' => '送足払', 'english' => 'Sliding foot sweep', 'gokyo' => 2, 'prohibited' => false, 'notes' => null],
                ['name' => 'Uchi-mata', 'kanji' => '内股', 'english' => 'Inner-thigh throw', 'gokyo' => 2, 'prohibited' => false, 'notes' => null],
                ['name' => 'Ko-soto-gake', 'kanji' => '小外掛', 'english' => 'Minor outer hook', 'gokyo' => 3, 'prohibited' => false, 'notes' => null],
                ['name' => 'Ashi-guruma', 'kanji' => '足車', 'english' => 'Leg wheel', 'gokyo' => 3, 'prohibited' => false, 'notes' => null],
                ['name' => 'Harai-tsurikomi-ashi', 'kanji' => '払釣込足', 'english' => 'Lift-pull foot sweep', 'gokyo' => 3, 'prohibited' => false, 'notes' => null],
                ['name' => 'O-guruma', 'kanji' => '大車', 'english' => 'Major wheel', 'gokyo' => 4, 'prohibited' => false, 'notes' => null],
                ['name' => 'O-soto-guruma', 'kanji' => '大外車', 'english' => 'Major outer wheel', 'gokyo' => 5, 'prohibited' => false, 'notes' => null],
                ['name' => 'O-soto-otoshi', 'kanji' => '大外落', 'english' => 'Major outer drop', 'gokyo' => null, 'prohibited' => false, 'notes' => null],
                ['name' => 'Tsubame-gaeshi', 'kanji' => '燕返', 'english' => 'Swallow counter', 'gokyo' => null, 'prohibited' => false, 'notes' => null],
                ['name' => 'O-soto-gaeshi', 'kanji' => '大外返', 'english' => 'Major outer reap counter', 'gokyo' => null, 'prohibited' => false, 'notes' => null],
                ['name' => 'O-uchi-gaeshi', 'kanji' => '大内返', 'english' => 'Major inner reap counter', 'gokyo' => null, 'prohibited' => false, 'notes' => null],
                ['name' => 'Hane-goshi-gaeshi', 'kanji' => '跳腰返', 'english' => 'Spring hip counter', 'gokyo' => null, 'prohibited' => false, 'notes' => null],
                ['name' => 'Harai-goshi-gaeshi', 'kanji' => '払腰返', 'english' => 'Sweeping hip counter', 'gokyo' => null, 'prohibited' => false, 'notes' => null],
                ['name' => 'Uchi-mata-gaeshi', 'kanji' => '内股返', 'english' => 'Inner-thigh counter', 'gokyo' => null, 'prohibited' => false, 'notes' => null],
            ]],
            ['slug' => 'ma-sutemi', 'name' => 'Ma-sutemi-waza', 'kanji' => '真捨身技', 'english' => 'Rear sacrifice techniques', 'family' => Family::Nage, 'techniques' => [
                ['name' => 'Tomoe-nage', 'kanji' => '巴投', 'english' => 'Circle throw', 'gokyo' => 3, 'prohibited' => false, 'notes' => null],
                ['name' => 'Sumi-gaeshi', 'kanji' => '隅返', 'english' => 'Corner reversal', 'gokyo' => 4, 'prohibited' => false, 'notes' => null],
                ['name' => 'Hikikomi-gaeshi', 'kanji' => '引込返', 'english' => 'Pull-in reversal', 'gokyo' => null, 'prohibited' => false, 'notes' => null],
                ['name' => 'Tawara-gaeshi', 'kanji' => '俵返', 'english' => 'Rice-bale reversal', 'gokyo' => null, 'prohibited' => false, 'notes' => null],
                ['name' => 'Ura-nage', 'kanji' => '裏投', 'english' => 'Back throw', 'gokyo' => 5, 'prohibited' => false, 'notes' => null],
            ]],
            ['slug' => 'yoko-sutemi', 'name' => 'Yoko-sutemi-waza', 'kanji' => '横捨身技', 'english' => 'Side sacrifice techniques', 'family' => Family::Nage, 'techniques' => [
                ['name' => 'Yoko-otoshi', 'kanji' => '横落', 'english' => 'Side drop', 'gokyo' => 3, 'prohibited' => false, 'notes' => null],
                ['name' => 'Tani-otoshi', 'kanji' => '谷落', 'english' => 'Valley drop', 'gokyo' => 4, 'prohibited' => false, 'notes' => null],
                ['name' => 'Hane-makikomi', 'kanji' => '跳巻込', 'english' => 'Springing wraparound', 'gokyo' => 4, 'prohibited' => false, 'notes' => null],
                ['name' => 'Soto-makikomi', 'kanji' => '外巻込', 'english' => 'Outer wraparound', 'gokyo' => 4, 'prohibited' => false, 'notes' => null],
                ['name' => 'Uchi-makikomi', 'kanji' => '内巻込', 'english' => 'Inner wraparound', 'gokyo' => null, 'prohibited' => false, 'notes' => null],
                ['name' => 'Uki-waza', 'kanji' => '浮技', 'english' => 'Floating throw', 'gokyo' => 5, 'prohibited' => false, 'notes' => null],
                ['name' => 'Yoko-wakare', 'kanji' => '横分', 'english' => 'Side separation', 'gokyo' => 5, 'prohibited' => false, 'notes' => null],
                ['name' => 'Yoko-guruma', 'kanji' => '横車', 'english' => 'Side wheel', 'gokyo' => 5, 'prohibited' => false, 'notes' => null],
                ['name' => 'Yoko-gake', 'kanji' => '横掛', 'english' => 'Side hook', 'gokyo' => 5, 'prohibited' => false, 'notes' => null],
                ['name' => 'Daki-wakare', 'kanji' => '抱分', 'english' => 'High separation', 'gokyo' => null, 'prohibited' => false, 'notes' => null],
                ['name' => 'O-soto-makikomi', 'kanji' => '大外巻込', 'english' => 'Major outer wraparound', 'gokyo' => null, 'prohibited' => false, 'notes' => null],
                ['name' => 'Uchi-mata-makikomi', 'kanji' => '内股巻込', 'english' => 'Inner-thigh wraparound', 'gokyo' => null, 'prohibited' => false, 'notes' => null],
                ['name' => 'Harai-makikomi', 'kanji' => '払巻込', 'english' => 'Sweeping wraparound', 'gokyo' => null, 'prohibited' => false, 'notes' => null],
                ['name' => 'Ko-uchi-makikomi', 'kanji' => '小内巻込', 'english' => 'Minor inner wraparound', 'gokyo' => null, 'prohibited' => false, 'notes' => null],
                ['name' => 'Kani-basami', 'kanji' => '蟹挟', 'english' => 'Crab scissors', 'gokyo' => null, 'prohibited' => true, 'notes' => null],
                ['name' => 'Kawazu-gake', 'kanji' => '河津掛', 'english' => 'One-leg entanglement', 'gokyo' => null, 'prohibited' => true, 'notes' => null],
            ]],
            ['slug' => 'osaekomi', 'name' => 'Osaekomi-waza', 'kanji' => '抑込技', 'english' => 'Pins', 'family' => Family::Katame, 'techniques' => [
                ['name' => 'Kesa-gatame', 'kanji' => '袈裟固', 'english' => 'Scarf hold', 'gokyo' => null, 'prohibited' => false, 'notes' => null],
                ['name' => 'Kuzure-kesa-gatame', 'kanji' => '崩袈裟固', 'english' => 'Modified scarf hold', 'gokyo' => null, 'prohibited' => false, 'notes' => null],
                ['name' => 'Ushiro-kesa-gatame', 'kanji' => '後袈裟固', 'english' => 'Reverse scarf hold', 'gokyo' => null, 'prohibited' => false, 'notes' => null],
                ['name' => 'Kata-gatame', 'kanji' => '肩固', 'english' => 'Shoulder hold', 'gokyo' => null, 'prohibited' => false, 'notes' => null],
                ['name' => 'Kami-shiho-gatame', 'kanji' => '上四方固', 'english' => 'Upper four-quarter hold', 'gokyo' => null, 'prohibited' => false, 'notes' => null],
                ['name' => 'Kuzure-kami-shiho-gatame', 'kanji' => '崩上四方固', 'english' => 'Modified upper four-quarter hold', 'gokyo' => null, 'prohibited' => false, 'notes' => null],
                ['name' => 'Yoko-shiho-gatame', 'kanji' => '横四方固', 'english' => 'Side four-quarter hold', 'gokyo' => null, 'prohibited' => false, 'notes' => null],
                ['name' => 'Tate-shiho-gatame', 'kanji' => '縦四方固', 'english' => 'Vertical four-quarter hold', 'gokyo' => null, 'prohibited' => false, 'notes' => null],
                ['name' => 'Uki-gatame', 'kanji' => '浮固', 'english' => 'Floating hold', 'gokyo' => null, 'prohibited' => false, 'notes' => null],
                ['name' => 'Ura-gatame', 'kanji' => '裏固', 'english' => 'Back hold', 'gokyo' => null, 'prohibited' => false, 'notes' => null],
            ]],
            ['slug' => 'shime', 'name' => 'Shime-waza', 'kanji' => '絞技', 'english' => 'Strangles', 'family' => Family::Katame, 'techniques' => [
                ['name' => 'Nami-juji-jime', 'kanji' => '並十字絞', 'english' => 'Normal cross strangle', 'gokyo' => null, 'prohibited' => false, 'notes' => null],
                ['name' => 'Gyaku-juji-jime', 'kanji' => '逆十字絞', 'english' => 'Reverse cross strangle', 'gokyo' => null, 'prohibited' => false, 'notes' => null],
                ['name' => 'Kata-juji-jime', 'kanji' => '片十字絞', 'english' => 'Half cross strangle', 'gokyo' => null, 'prohibited' => false, 'notes' => null],
                ['name' => 'Hadaka-jime', 'kanji' => '裸絞', 'english' => 'Naked strangle', 'gokyo' => null, 'prohibited' => false, 'notes' => null],
                ['name' => 'Okuri-eri-jime', 'kanji' => '送襟絞', 'english' => 'Sliding collar strangle', 'gokyo' => null, 'prohibited' => false, 'notes' => null],
                ['name' => 'Kata-ha-jime', 'kanji' => '片羽絞', 'english' => 'Single-wing strangle', 'gokyo' => null, 'prohibited' => false, 'notes' => null],
                ['name' => 'Katate-jime', 'kanji' => '片手絞', 'english' => 'One-hand strangle', 'gokyo' => null, 'prohibited' => false, 'notes' => null],
                ['name' => 'Ryote-jime', 'kanji' => '両手絞', 'english' => 'Two-hand strangle', 'gokyo' => null, 'prohibited' => false, 'notes' => null],
                ['name' => 'Sode-guruma-jime', 'kanji' => '袖車絞', 'english' => 'Sleeve wheel strangle', 'gokyo' => null, 'prohibited' => false, 'notes' => null],
                ['name' => 'Tsukkomi-jime', 'kanji' => '突込絞', 'english' => 'Thrust strangle', 'gokyo' => null, 'prohibited' => false, 'notes' => null],
                ['name' => 'Sankaku-jime', 'kanji' => '三角絞', 'english' => 'Triangle strangle', 'gokyo' => null, 'prohibited' => false, 'notes' => null],
                ['name' => 'Do-jime', 'kanji' => '胴絞', 'english' => 'Trunk strangle', 'gokyo' => null, 'prohibited' => true, 'notes' => null],
            ]],
            ['slug' => 'kansetsu', 'name' => 'Kansetsu-waza', 'kanji' => '関節技', 'english' => 'Joint locks', 'family' => Family::Katame, 'techniques' => [
                ['name' => 'Ude-garami', 'kanji' => '腕緘', 'english' => 'Entangled arm lock', 'gokyo' => null, 'prohibited' => false, 'notes' => null],
                ['name' => 'Ude-hishigi-juji-gatame', 'kanji' => '腕挫十字固', 'english' => 'Cross arm lock', 'gokyo' => null, 'prohibited' => false, 'notes' => null],
                ['name' => 'Ude-hishigi-ude-gatame', 'kanji' => '腕挫腕固', 'english' => 'Straight arm lock', 'gokyo' => null, 'prohibited' => false, 'notes' => null],
                ['name' => 'Ude-hishigi-hiza-gatame', 'kanji' => '腕挫膝固', 'english' => 'Knee arm lock', 'gokyo' => null, 'prohibited' => false, 'notes' => null],
                ['name' => 'Ude-hishigi-waki-gatame', 'kanji' => '腕挫腋固', 'english' => 'Armpit arm lock', 'gokyo' => null, 'prohibited' => false, 'notes' => null],
                ['name' => 'Ude-hishigi-hara-gatame', 'kanji' => '腕挫腹固', 'english' => 'Stomach arm lock', 'gokyo' => null, 'prohibited' => false, 'notes' => null],
                ['name' => 'Ude-hishigi-ashi-gatame', 'kanji' => '腕挫脚固', 'english' => 'Leg arm lock', 'gokyo' => null, 'prohibited' => false, 'notes' => null],
                ['name' => 'Ude-hishigi-te-gatame', 'kanji' => '腕挫手固', 'english' => 'Hand arm lock', 'gokyo' => null, 'prohibited' => false, 'notes' => null],
                ['name' => 'Ude-hishigi-sankaku-gatame', 'kanji' => '腕挫三角固', 'english' => 'Triangle arm lock', 'gokyo' => null, 'prohibited' => false, 'notes' => null],
                ['name' => 'Ashi-garami', 'kanji' => '足緘', 'english' => 'Leg entanglement', 'gokyo' => null, 'prohibited' => true, 'notes' => null],
            ]],
        ];
    }
}
