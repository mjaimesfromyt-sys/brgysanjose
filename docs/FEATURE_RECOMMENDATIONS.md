# Barangay San Jose System — Feature Recommendations

Practical, high-value features grouped by user role. Each item notes the effort level:
🟢 quick win (days) · 🟡 medium (1–2 weeks) · 🔴 major (capstone-plus / future dev).

---

## Super-Admin (overall control, audit, oversight)

1. **🟢 Role & permission matrix UI** — manage what sub-admins can do from a screen instead of code (e.g., "Secretary can approve documents but not refunds"). Laravel gates/policies already exist; this adds the UI on top.
2. **🟢 Dashboard analytics widgets** — daily/weekly/monthly income from bookings, rentals, and document fees; top facilities; peak request types. The transaction history data is already there — it needs charts (Chart.js is already in the stack via the admin theme).
3. **🟢 Backup & export center** — one-click download of the database dump and uploaded files (ID photos, seals). Critical for a system holding resident records; also simplifies future hosting moves like the one just completed.
4. **🟡 Full audit trail browser** — the `activity_log` table is already populated on every action. Add a searchable/filterable viewer (who approved what, when, from which IP) — strong evidence trail and accountability.
5. **🟡 Staff accounts & terminal audit** — track which admin collected each cash payment (already partly done) and require a remark when marking paid after the fact.
6. **🟡 Announcement scheduling & targeting** — publish announcements to specific puroks, or schedule them for fiesta week etc.
7. **🔴 Multi-barangay mode** — the codebase is already close (settings table, seal images, header/footer). Generalizing it makes the capstone demo-able as a product for other barangays.

## Sub-Admin / Staff (day-to-day operations)

1. **🟢 Mandatory structured rejection reasons** *(done)* — rejecting a booking, rental, document request, or resident account now requires a written reason, shown in a dedicated "Reason for rejection" column and sent to the resident.
2. **🟢 Print queue for released documents** — a "print next" worklist so the secretary processes validated requests without hunting through tabs.
2. **🟢 Claim-code scanner/lookup** — type or scan the resident's claim code (BRGY-2026-XXXX) to instantly pull up their request for release. Already stored — just needs a fast lookup box.
3. **🟡 Walk-in (offline) request encoding** — let staff create requests for residents who show up in person without an account, printing the same PDF.
4. **🟡 Facility & equipment calendar view** — month calendar showing bookings per facility and equipment stock per date, so "fully booked" is visible at a glance before the resident even applies.
5. **🟡 Low-stock & maintenance flags for equipment** — mark chairs/tables as "under repair" so they aren't bookable; alert when usable stock drops below a threshold.
6. **🟡 SMS notifications (optional add-on)** — many residents don't check email; an SMS gateway (e.g., PhilSMS/semaphore) for approval and claim-ready notices.
7. **🟢 Daily cash summary report** — end-of-day printable total of cash collected per collector, closing the loop on the income columns already tracked.

## Residents (public end)

1. **🟢 Request status timeline** — a visual tracker (Submitted → Paid → Validated → Ready to Claim) on each request so residents stop calling the hall.
2. **🟢 Downloadable receipt / proof of payment** — for PayMongo payments, a small PDF receipt with the reference number (the data is already stored).
3. **🟡 Re-request in one click** — repeat a previous document request with the same details (great for the clearance users get monthly for job applications).
4. **🟡 Family/household portal** — head of family sees dependents' documents and requests for them (useful for senior/PWD household members).
5. **🟡 Appointment reminders** — email/SMS the day before a confirmed captain appointment or facility booking.
6. **🟡 Community bulletin with comment reactions** — lightweight engagement on announcements (moderated).
7. **🔴 Mobile app (PWA first)** — the site is already responsive; adding a PWA manifest + install prompt + push notifications gives an "app" without app-store costs. A manifest and icons already exist in `public/`.

---

## Suggested priority for the capstone defense

If picking 5 to demo next: **role & permission matrix**, **analytics dashboard**, **request status timeline**, **facility calendar**, **PWA install + push**. Together they show breadth across all three roles with mostly existing data — no new infrastructure required.

---

*Context: this document was written after the InfinityFree migration, the paid-reject workflow fixes (bookings, rentals), the residents table split (Address vs Verification/ID), and the certificate PDF overhaul (header, footer, seal watermark, scannable PNG QR). Updated after the Decision→Action column rename and the mandatory rejection-reason rollout.*
