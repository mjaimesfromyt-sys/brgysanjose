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
