# EU Withdrawal Compliance

WordPress plugin that adds the EU online withdrawal function required by Directive (EU) 2023/2673, mandatory from 19 June 2026 for every online retailer in the European Union.

## What it does

- Creates a public withdrawal page automatically on activation, with a fully-escaped form embedded via the `[ayudawp_withdrawal_form]` shortcode. The page is pre-filled with a neutral, multilingual sample template (with a clear "review with a legal advisor" disclaimer) so you can publish it after a quick review.
- Adds a "Right of withdrawal" endpoint inside WooCommerce **My Account** with a per-order "Withdraw" button shown only while the 14-day window is open.
- Injects an "Exercise withdrawal right here" notice with link to the form inside WooCommerce transactional emails (processing, on-hold and completed orders), to comply with the trader's obligation to inform consumers about the existence and placement of the withdrawal function.
- Validates the email/order pair against the WooCommerce database, including the 14-day deadline check.
- Adds a private order note to the WooCommerce order linking the request to the log entry.
- Sends confirmation email to the customer and notification email to the shop admin, with reply-to pointing to the customer.
- Logs every request as a private custom post type with status tracking (pending, accepted, rejected, completed), IP and user agent for legal traceability.
- Integrates inside the WooCommerce admin menu when WooCommerce is active (settings live at **WooCommerce → EU Withdrawal**, request log at **WooCommerce → Withdrawals**). Falls back to a top-level menu when running standalone.

## Requirements

- WordPress 6.0 or higher
- PHP 7.4 or higher
- WooCommerce 7.0 or higher (recommended)

## Installation

### From GitHub release

1. Download the latest `.zip` from the [Releases page](https://github.com/fernandotellado/eu-withdrawal-compliance/releases).
2. In your WordPress admin, go to **Plugins → Add New → Upload Plugin**.
3. Upload the zip and activate.

### From source

```bash
git clone https://github.com/fernandotellado/eu-withdrawal-compliance.git
```

Move the folder to `wp-content/plugins/` and activate from the WordPress admin.

## Configuration

After activation:

1. The plugin creates a "Right of withdrawal" page automatically with a sample legal template. Review and edit it from **Pages**.
2. Go to **WooCommerce → EU Withdrawal** (or **Withdrawals → Settings** without WooCommerce) to configure the notification email and the page that hosts the form.
3. Add the URL of the withdrawal page to your footer or legal links so it is visible from any page on your site.

## Filters and hooks

| Filter | Purpose |
|--------|---------|
| `ayudawp_euw_grace_days` | Number of additional days to add to the 14-day deadline check. Default: 0. |
| `ayudawp_euw_skip_deadline_check` | Return `true` to disable the 14-day check entirely. Default: `false`. |
| `ayudawp_euw_email_ids` | Array of WooCommerce email IDs where the withdrawal notice is injected. Default: `customer_processing_order`, `customer_on_hold_order`, `customer_completed_order`. |

| Action | Purpose |
|--------|---------|
| `ayudawp_euw_after_submission` | Fires after a withdrawal request has been processed. Receives the CPT ID and the submission data array. |

## Compliance status

This plugin implements the **minimum compliant version** of EU Directive 2023/2673. It works on all member states from 19 June 2026.

The German interpretation of the directive (the strictest known so far) requires a two-step confirmation flow: a first button that opens the function, an intermediate page with the customer's data, and a second "confirm withdrawal" button that submits the request. This is not yet implemented because Spanish transposition is still pending as of May 2026 and a future update may be required to align with the final Spanish Real Decreto.

## License

GPL-2.0-or-later. See [LICENSE](LICENSE).

## Author

Created and maintained by Fernando Tellado at [AyudaWP.com](https://ayudawp.com).

For installation, configuration or custom development services, see [mantenimiento.ayudawp.com](https://mantenimiento.ayudawp.com).

## Contributing

Bug reports, pull requests and translations are welcome. Open an issue first to discuss any non-trivial change.
