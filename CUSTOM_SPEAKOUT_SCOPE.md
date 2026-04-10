# Custom SpeakOut Scope and Estimates

This plugin is the custom free replacement for SpeakOut used by Animal Victory.
It keeps existing data compatibility by preserving the current database schema and option keys.

## Delivery Phases

### 1) MVP Build (Core Replacement)

Target outcomes:
- Petition creation and management
- Signature form per petition
- Signature capture and listing
- Signature count display
- CSV export
- Thank-you email with share and manage links
- Read More and Sign Petition flow

Estimated effort:
- 3 to 5 weeks

### 2) Enhanced Features

Target outcomes:
- Self-service comment edit
- Self-service signature removal
- Optional sign-in hint and prefill support
- Improved admin CSV filtering experience

Estimated effort:
- 2 to 3 weeks

### 3) Productization

Target outcomes:
- Custom branding and no license lock
- Performance hardening for high-volume petitions
- Extensibility and diagnostics support
- Documentation and release readiness

Estimated effort:
- 2 to 4 weeks

## Data Migration and Safety Notes

- Existing data is preserved by design.
- Existing tables are reused:
  - `wp_dk_speakout_petitions`
  - `wp_dk_speakout_signatures`
- Existing option keys are reused (`dk_speakout_*`) for compatibility.
- Do not run destructive uninstall cleanup on production if you need to keep historical data.
