# TransitOps - Vehicle Module TODO

- [x] Step 0: Module 01 Project Setup foundations (CSRF helper + AJAX CSRF enforcement normalization)

- [ ] Step 1: Create enterprise vehicle helpers
  - [ ] functions/vehicle_functions.php
  - [ ] functions/validation_functions.php (vehicle-specific validation helpers)
  - [x] functions/upload_functions.php (document upload + vehicle photo resize/compress + thumbnail)


- [ ] Step 2: Implement Vehicle module pages
  - [ ] modules/vehicles/list.php
  - [ ] modules/vehicles/add.php
  - [ ] modules/vehicles/edit.php
  - [ ] modules/vehicles/view.php
  - [ ] modules/vehicles/delete.php
  - [ ] modules/vehicles/documents.php
  - [ ] modules/vehicles/history.php
  - [ ] modules/vehicles/status.php

- [ ] Step 3: Implement AJAX endpoints (existing `ajax/` folder)
  - [ ] ajax/vehicle.php (unified action router)
  - [ ] ajax/vehicle_search.php
  - [ ] ajax/vehicle_status.php
  - [ ] ajax/vehicle_delete.php
  - [ ] ajax/vehicle_export.php

- [ ] Step 4: Implement Vehicle module assets
  - [ ] assets/css/vehicle.css
  - [ ] assets/css/vehicle-table.css
  - [ ] assets/css/vehicle-form.css
  - [ ] assets/js/vehicle.js
  - [ ] assets/js/vehicle-validation.js
  - [ ] assets/js/vehicle-ajax.js

- [ ] Step 5: Database updates (only if needed)
  - [ ] Verify schema supports documents + history + strict assignment rules
  - [ ] Add/adjust indexes, constraints, views, stored procedures, triggers only if missing

- [ ] Step 6: Integrate with sidebar actions (if missing)
  - [ ] Ensure sidebar links for Vehicles -> Vehicle List/Add/Documents

- [ ] Step 7: Basic manual QA checklist
  - [ ] Role permission gating
  - [ ] CSRF protection on all POST actions
  - [ ] Validation + duplicate checks
  - [ ] Upload validation
  - [ ] Status transition business rules
  - [ ] Export endpoints

