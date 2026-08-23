## The seam between Person A (identity) and Person B (anonymous voting)

There is exactly one connection point between the two halves of this system:
the `issueToken(int $electionId): string` function, in `api/lib/IssueToken.php`.

**Rules that must never change, even under deadline pressure:**

1. `issueToken()` accepts **only** `election_id`. It must never be changed to
   accept `voter_id`, `phone_number`, `aadhaar_hash`, or any other field that
   identifies a person. That omission is not an oversight — it's the entire
   anonymity guarantee of the system.
2. Person A's code calls this function once, at the moment
   `VOTING_INTENT.status` becomes `otp_verified`. It gets back a `token_hash`
   string and hands that directly to the voter's app. **Person A's side does
   not store this token_hash anywhere** — once it's handed to the app, A's
   database has no further record of which token went to which voter.
3. From that point on, the token is the *only* thing the anonymous side
   (`CREDENTIAL_TOKEN`, `BALLOT`, `AUDIT_LOG`) ever sees. No table in Person
   B's schema has a foreign key to `VOTER`, `VOTING_INTENT`, `DEVICE_BINDING`,
   `TIME_SLOT`, or `OTP_CHALLENGE` — check this stays true on every PR that
   touches the schema.

**How to verify the boundary hasn't been broken** (run this after any schema change):

```sql
SELECT TABLE_NAME, COLUMN_NAME, REFERENCED_TABLE_NAME, REFERENCED_COLUMN_NAME
FROM information_schema.KEY_COLUMN_USAGE
WHERE TABLE_SCHEMA = 'voting_system' AND REFERENCED_TABLE_NAME IS NOT NULL;
```

Every row involving `CREDENTIAL_TOKEN`, `BALLOT`, or `AUDIT_LOG` should only
ever reference each other or `ELECTION` — never an identity table.

**Day 1 test-endpoint note:** `api/issue-token.php` is a temporary public
wrapper around `issueToken()`, added only so Person A can hit it from Postman
before their own `/otp-verify` handler calls the function directly in-process.
Remove it (or lock it behind an internal-only check) before Day 3 — it
shouldn't exist as a public route in the real flow.
