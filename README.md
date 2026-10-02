# Waza Atlas

Search all 100 Kodokan judo techniques and watch them as 3D point-light motion, in the style of PLAViMoP.

- **Catalogue:** 68 nage-waza and 32 katame-waza in 8 categories, with kanji, English names, Gokyo groups and the techniques prohibited in shiai.
- **Search:** works on romaji, English, kanji and category names, ignoring case, spaces and hyphens. "te waza", "Te-waza" and "hand techniques" all return the 16 te-waza, and "踵" finds Kibisu-gaeshi.
- **Viewer:** a canvas renderer with no dependencies. Tori and uke are shown as glowing joints. You can orbit, zoom, switch views, play at ¼× or ½× speed, turn on trails and bones, and jump through the phases kumikata, kuzushi, tsukuri, kake and ukemi.
- **Motions:** one per technique, stored as a file. The viewer plays this app's keyframe JSON. BVH and C3D files (from mocap suits, pose-estimation tools or PLAViMoP) can already be uploaded, validated and served, and are ready for a viewer that plays them.

Stack: PHP 8.2+, Symfony 7.4 LTS, Doctrine ORM 3, SQLite by default, Twig, PHPUnit 11.

## Getting started

```bash
composer install
composer setup            # creates the schema and loads the catalogue + bundled motions
php -S 127.0.0.1:8000 -t public    # or: symfony serve
```

Open http://127.0.0.1:8000, or go straight to a technique at http://127.0.0.1:8000/waza/kibisu-gaeshi.

To use MySQL or PostgreSQL instead of SQLite, set `DATABASE_URL` in `.env.local`, then run `composer setup`.

Running `bin/console app:seed` again is safe: it updates the techniques in place and leaves existing motions alone. Add `--replace-motions` to reload the files in `data/motions/`.

## Tests

```bash
composer test
```

The 25 tests cover search, filters, the category tree, the JSON error format, seeding twice, the page itself, and motion upload, validation and deletion for BVH, C3D and keyframes.

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

Then send the file as multipart form data:

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
    "tori": [[0, {"z": -0.45, "bend": 8, "ik_r_lapel": 1}], [1.95, {"bend": 52}]],
    "uke":  [[0, {"z": 0.45, "yaw": 180}], [2.95, {"pitch": -58}]]
  }
}
```

Each key is `[seconds, pose changes]`. A pose change only lists the values that differ from the previous key, and the viewer blends between keys with Hermite curves. Pose values:

- **Body:** `x`, `y`, `z` (metres), and `yaw`, `pitch`, `roll`, `bend`, `twist` and `head` in degrees.
- **Legs:** hip flexion and abduction per side (`lHipF`, `lHipA`, `rHipF`, `rHipA`) and knee bend (`lKnee`, `rKnee`).
- **Arms:** shoulder flexion and abduction (`lShF`, `lShA`, `rShF`, `rShA`) and elbow bend (`lElb`, `rElb`).
- **Grips:** `ik_<l|r>_<sleeve|heel|lapel>`, a weight from 0 to 1 that pulls that hand onto the partner's sleeve, heel or lapel with two-bone IK.
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
public/assets/                     atlas.css, atlas.js (viewer, no build step)
data/motions/                      bundled motions, loaded by app:seed
var/motions/                       uploaded motions (git-ignored)
```

## Next steps

1. **Play BVH in the viewer.** Parse the hierarchy in `atlas.js`, map its joints onto the 15 points the viewer already draws, and use the frame time for playback.
2. **Record real throws.** Film a few common Gokyo throws from two angles and turn the video into BVH with a markerless pose tool, then upload the files through the API.
3. **Add an admin screen** for uploads, so recording a motion doesn't need curl.
