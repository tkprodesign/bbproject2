# Backend Control Panel Change Log

This log records changes to shared functionality across the Support, Admin and Master control panels. Unless explicitly marked role-specific, a shared backend change must remain synchronized across all three panels.

## 2026-10-01

### Customer investigation restriction — shared
- Added customer-level Restricted access state.
- Added mandatory restriction reason.
- Added restricted-by and restricted-at metadata.
- Added security-event audit records.
- Added customer restriction and restoration email notices.
- Added a brief restricted-access message on blocked login attempts.
- Added Restore customer access action.
- Preserved customer accounts, balances, transactions, KYC and support records during restriction.

### Customer session revocation — shared
- Replaced browser-held email identity trust with server-side customer session authentication.
- Added session-version validation to customer access.
- Added opaque remember-me tokens stored server-side as hashes.
- Restriction and suspension revoke remember tokens and invalidate prior customer sessions.
- Password change invalidates other sessions while establishing a fresh current session.
- Password reset invalidates all prior customer sessions and persistent tokens.

### Management-review conversion — site-wide
- Removed visible demo presentation from canonical public/customer/backend surfaces.
- Kept search-engine blocking in place for private management review.
- Removed customer-specific demo seeding from the production dashboard path.
- Canonicalized retired v2/review routes.
- Added live deployment smoke checks for public review routes and customer-auth schema.
