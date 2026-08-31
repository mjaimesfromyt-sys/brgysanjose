# Official seals

Drop the two seal images here, with exactly these filenames:

| File | What it is |
|---|---|
| `barangay-seal.png` | The round **Barangay San Jose** seal (green ring, "TALIBON, BOHOL", 1910) |
| `talibon-seal.png`  | The round **Municipality of Talibon** seal (yellow ring, blue triangle) |

Notes:

- **PNG with a transparent background** looks best — the seals sit on a tinted
  hero band and on white cards.
- Square images, ideally around **512×512**. They are displayed at roughly
  56–112px, so anything above that is plenty; much larger just costs load time.
- Keep the filenames exactly as above. `resources/views/partials/seal.blade.php`
  looks for these paths.
- Until the files exist, the site falls back to the plain "SJ" monogram rather
  than showing a broken image, so nothing looks broken while you add them.

Because `public/build` is gitignored but `public/images` is not, these commit
normally — no `git add -f` needed.

## Icons generated from the barangay seal

`barangay-seal.png` is also the source for the site's browser icons, which live
in `public/` and are wired up by `resources/views/partials/favicon.blade.php`:

| File | Size | Used for |
|---|---|---|
| `favicon.ico` | 16, 32, 48 | the browser tab (and the bare `/favicon.ico` request) |
| `favicon-16x16.png`, `favicon-32x32.png`, `favicon-48x48.png` | as named | modern browsers |
| `apple-touch-icon.png` | 180 | iOS home screen — flattened onto white, since iOS fills transparency with black |
| `icon-192.png`, `icon-512.png` | as named | `site.webmanifest`, for Android home screens |

The seal is padded to a square before scaling so it is not squashed. Replace the
whole set if `barangay-seal.png` is ever swapped out — the emails embed the
seal itself, but these are separate copies.
