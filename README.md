# Waza Atlas

Search all 100 Kodokan judo techniques and watch them as 3D point-light motion, in the style of PLAViMoP.

- **Catalogue:** 68 nage-waza and 32 katame-waza in 8 categories, with kanji, English names, Gokyo groups and the techniques prohibited in shiai.
- **Search:** works on romaji, English, kanji and category names, ignoring case, spaces and hyphens. "te waza", "Te-waza" and "hand techniques" all return the 16 te-waza, and "踵" finds Kibisu-gaeshi.
- **Viewer:** a canvas renderer with no dependencies. Tori and uke are shown as glowing joints. You can orbit, zoom, switch views, play at ¼× or ½× speed, turn on trails and bones, and jump through the phases kumikata, kuzushi, tsukuri, kake and ukemi.
- **Motions:** all 16 te-waza come with a hand-keyed motion, a five-phase timeline (kumikata, kuzushi, tsukuri, kake, ukemi) and a written breakdown of each phase. A technique has one motion, stored as a file. The viewer plays this app's keyframe JSON. BVH and C3D files (from mocap suits, pose-estimation tools or PLAViMoP) can already be uploaded, validated and served, and are ready for a viewer that plays them.

Stack: PHP 8.4, Symfony 7.4 LTS, Doctrine ORM 3 + Migrations, SQLite by default, Twig, PHPUnit 11. Runs in Docker on FrankenPHP.

## Getting started with Docker

You need Docker with Compose v2. The image runs PHP 8.4. `composer.json` pins Composer's target platform to PHP 8.4.1 (`config.platform.php`), so a `composer install` on your own machine picks the same package versions as the container, whatever PHP you have locally. The first install creates `composer.lock`; commit it so every build installs the same versions. Doctrine uses PHP 8.4's native lazy objects (`enable_native_lazy_objects`), so the app needs PHP 8.4 or newer.

```bash
docker compose build
docker compose up -d --wait
```

Open http://localhost:8080. On first start the container runs the migrations and loads the catalogue. The dev setup mounts your working copy, so code changes show up on refresh. The SQLite database stays in `var/data.db` and uploads go to `var/motions/`, as they would without Docker. Uploads work straight away with the token `dev-token`.

| Command | What it does |
|---|---|
| `make up` / `make down` | Start or stop the dev stack |
| `make logs` | Follow the logs |
| `make sh` | Open a shell in the container |
| `make test` | Run PHPUnit in the container |
| `make console c="debug:router"` | Run any `bin/console` command |
| `make seed` | Reload the catalogue and the bundled motions |

Change the port with `HTTP_PORT=8000 make up`.

### Production

The `frankenphp_prod` image has the code, the production dependencies and a warmed cache built in. It runs as a non-root user with opcache locked. The database and uploaded motions live on a named volume (`storage`, mounted at `/srv/storage`), so they survive rebuilds and upgrades.

```bash
export APP_SECRET=$(openssl rand -hex 32)
export MOTION_UPLOAD_TOKEN=$(openssl rand -hex 24)
export SERVER_NAME=waza.example.com     # your domain; Caddy gets the HTTPS certificate itself
make prod                               # = docker compose -f compose.yaml -f compose.prod.yaml up -d --build --wait
```

Leave `SERVER_NAME` unset to serve plain HTTP on port 80, for example behind your own reverse proxy or load balancer. The stack won't start without `APP_SECRET`.

Each container start runs any new migrations and then the idempotent seed. Set `RUN_MIGRATIONS=0` or `SEED_CATALOGUE=0` to skip either, for example when you run several replicas and migrate in a separate step.

`/healthz` returns `{"status":"ok"}` when the app can reach its database. Point uptime monitoring at it. The container's own healthcheck uses Caddy's admin endpoint, so it works with any `SERVER_NAME`.

To use Postgres or MySQL instead of SQLite, set `DATABASE_URL` (the image includes both drivers) and generate a fresh migration for that platform. The bundled migration is written for SQLite.

Back up the data volume:

```bash
docker run --rm -v waza-atlas_storage:/data -v "$PWD":/backup debian tar czf /backup/waza-storage.tgz -C /data .
```

## Getting started without Docker

You need PHP 8.4+ with pdo_sqlite.

```bash
composer install
composer setup            # runs the migrations and loads the catalogue + bundled motions
php -S 127.0.0.1:8000 -t public    # or: symfony serve
```

Open http://127.0.0.1:8000, or go straight to a technique at http://127.0.0.1:8000/waza/kibisu-gaeshi. PHP's built-in upload limit is 2 MB. To test larger motion files without Docker, add `-d upload_max_filesize=21M -d post_max_size=24M`.

To use MySQL or PostgreSQL instead of SQLite, set `DATABASE_URL` in `.env.local`, generate a migration for that platform with `bin/console doctrine:migrations:diff`, then run `composer setup`.

Running `bin/console app:seed` again is safe: it updates the techniques in place and leaves existing motions alone. Add `--replace-motions` to reload the files in `data/motions/`.

### Changing the schema

After editing an entity:

```bash
bin/console doctrine:migrations:diff
bin/console doctrine:migrations:migrate
```

A test fails if the entities and the migrations disagree, so a forgotten migration can't ship.

## Tests

```bash
composer test
```

The 42 tests cover search, filters, the category tree, the JSON error format, seeding twice, the page itself, motion upload, validation and deletion for BVH, C3D and keyframes, a check that every bundled motion is valid and fully described, and a check that the migrations match the entities.

## API

All responses are JSON. Errors look like `{"error": "No technique called \"x\".", "status": 404}`.

| Method | Path | What it does |
|---|---|---|
| GET | `/api/categories` | Families (nage, katame) with their categories and counts |
| GET | `/api/techniques` | Search and filter. Query params: `q`, `family` (`nage` or `katame`), `category` (e.g. `te`, `shime`), `motion=1` |
| GET | `/api/techniques/{slug}` | One technique, with its category, notes and motion metadata (format, source, duration, phases) |
| GET | `/api/techniques/{slug}/motion` | The motion file itself (keyframe JSON, BVH text or C3D binary) |
| POST | `/api/techniques/{slug}/motion` | Upload or replace a motion (needs the upload token) |
| DELETE | `/api/techniques/{slug}/motion` | Remove a motion (needs the upload token) |

Example search:

```bash
curl 'http://127.0.0.1:8000/api/techniques?q=te%20waza'
```

### Uploading a motion

Uploads are off until you set a token in `.env.local`:

```dotenv
MOTION_UPLOAD_TOKEN=pick-a-long-random-string
```

In Docker, pass it as an environment variable instead, as shown under Production. Then send the file as multipart form data:

```bash
curl -X POST http://127.0.0.1:8000/api/techniques/o-soto-gari/motion \
  -H 'Authorization: Bearer pick-a-long-random-string' \
  -F 'file=@o-soto-gari.bvh' \
  -F 'source=Rokoko suit, recorded at the club, Oct 2026' \
  -F 'phases=[{"t":0,"name":"Kumikata"},{"t":0.8,"name":"Kuzushi"},{"t":1.4,"name":"Kake","text":"Reap through the back of the leg."}]'
```

The file type comes from the extension: `.json`, `.bvh` or `.c3d`, up to 20 MB. Each file is checked before it is stored. BVH needs a `HIERARCHY` and a `MOTION` section, and C3D needs a valid header signature. The duration is read from the file.

### Keyframe format

`data/motions/kibisu-gaeshi.json` is the reference. In outline:

```json
{
  "format": "keyframes",
  "duration": 5,
  "source": "Hand-keyed placeholder",
  "phases": [{"t": 0, "name": "Kumikata", "en": "Gripping", "text": "…"}],
  "tracks": {
    "tori": [[0, {"z": -0.33, "bend": 8, "ik_r_lapel": 1}], [1.95, {"bend": 52}]],
    "uke":  [[0, {"z": 0.45, "yaw": 180}], [2.95, {"pitch": -58}]]
  }
}
```

Each key is `[seconds, pose changes]`. A pose change only lists the values that differ from the previous key, and the viewer blends between keys with Hermite curves. Pose values:

- **Body:** `x`, `y`, `z` (metres), and `yaw`, `pitch`, `roll`, `bend`, `twist` and `head` in degrees.
- **Legs:** hip flexion and abduction per side (`lHipF`, `lHipA`, `rHipF`, `rHipA`) and knee bend (`lKnee`, `rKnee`).
- **Arms:** shoulder flexion and abduction (`lShF`, `lShA`, `rShF`, `rShA`) and elbow bend (`lElb`, `rElb`).
- **Grips:** `ik_<l|r>_<target>`, a weight from 0 to 1 that pulls that hand onto the partner with two-bone IK. Targets: `sleeve`, `lapel`, `collar` (back of the collar), `shoulder` (upper arm by the armpit), `belt`, `beltback`, `knee` (back of the knee), `thigh` (inside of the thigh), `heel` and `wrist` (the partner's hand, for an arm being pulled along). A grip goes to the partner's opposite side by default, so tori's left hand takes uke's right sleeve. Add `_s` for the same side: `ik_r_thigh_s` is tori's right hand on uke's right thigh.
- **`plant`:** sets how strongly the figure is kept on the mat.

## Project layout

```
src/
  Catalogue/KodokanCatalogue.php   the 100 techniques, in Kodokan order
  Entity/                          WazaCategory, Technique, Motion (+ Family, MotionFormat enums)
  Repository/TechniqueRepository   search and filters
  Search/SearchNormalizer          "Te Waza" == "te-waza" == "tewaza"
  Motion/                          MotionInspector (validation) and MotionStorage (files + rows)
  Controller/Api/                  JSON API
  Controller/AtlasController       the page, including /waza/{slug} deep links
  Command/SeedCommand              bin/console app:seed
  Controller/HealthController      /healthz
migrations/                        Doctrine migrations
docker/frankenphp/                 Caddyfile, entrypoint, php.ini settings
Dockerfile, compose*.yaml          dev and prod images, compose stacks
public/assets/                     atlas.css, atlas.js (viewer, no build step)
data/motions/                      bundled motions, loaded by app:seed
var/motions/                       uploaded motions outside Docker (git-ignored)
```

## Animating more techniques

The 15 newer te-waza motions were written in `tools/motion-lab/src/` and checked with the lab in `tools/motion-lab`:

```bash
cd tools/motion-lab
npm install && npx playwright install chromium
node gen.js seoi-nage          # writes data/motions/seoi-nage.json, prints a report, renders sheets/seoi-nage.png
```

- **Report:** shows how close the two bodies get, whether uke ends on the mat, and any grip whose hand can't reach its target.
- **Sheet:** shows the motion from the side, front, three-quarter and top at 11 moments, so you can see a bad step or a head collision without opening the site.

The lab uses the viewer's own skeleton code, so what you check is what the site plays. Run `bin/console app:seed --replace-motions` afterwards to load changed files. The conventions are at the top of `src/shoulder-throws.js`:

- **Starting positions:** tori starts at z −0.33 facing +z and uke at +0.33 facing −z.
- **Pitch:** a positive pitch leans forward. A forward somersault ends on the back at pitch 270.
- **Legs follow the pitch:** to keep a leg vertical while leaning, add the pitch to its hip flexion.

## Next steps

1. **Play BVH in the viewer.** Parse the hierarchy in `atlas.js`, map its joints onto the 15 points the viewer already draws, and use the frame time for playback.
2. **Record real throws.** The hand-keyed motions show the idea of each throw, not a real judoka's timing. Film the Gokyo throws from two angles, turn the video into BVH with a markerless pose tool, and upload the files through the API.
3. **Animate the other 84 techniques** with the motion lab, starting with the rest of the Gokyo.
4. **Add an admin screen** for uploads, so recording a motion doesn't need curl.
