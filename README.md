# EU Withdrawal Compliance

WordPress plugin that adds the EU online withdrawal function required by Directive (EU) 2023/2673, mandatory from 19 June 2026 for every online retailer in the European Union.

🇬🇧 [English](#english) · 🇪🇸 [Español](#español)

---

## English

### What it does

- Creates a public withdrawal page automatically on activation, with a fully-escaped form embedded via the `[ayudawp_withdrawal_form]` shortcode. The page is pre-filled with a neutral, multilingual sample template (with a clear "review with a legal advisor" disclaimer) so you can publish it after a quick review.
- Adds a "Right of withdrawal" endpoint inside WooCommerce **My Account** with a per-order "Withdraw" button shown only while the 14-day window is open.
- Injects an "Exercise withdrawal right here" notice with link to the form inside WooCommerce transactional emails (processing, on-hold and completed orders), to comply with the trader's obligation to inform consumers about the existence and placement of the withdrawal function.
- Validates the email/order pair against the WooCommerce database, including the 14-day deadline check.
- Adds a private order note to the WooCommerce order linking the request to the log entry.
- Sends confirmation email to the customer and notification email to the shop admin, with reply-to pointing to the customer.
- Logs every request as a private custom post type with status tracking (pending, accepted, rejected, completed), IP and user agent for legal traceability.
- Integrates inside the WooCommerce admin menu when WooCommerce is active (settings live at **WooCommerce → EU Withdrawal**, request log at **WooCommerce → Withdrawals**). Falls back to a top-level menu when running standalone.

### Requirements

- WordPress 6.0 or higher
- PHP 7.4 or higher
- WooCommerce 7.0 or higher (recommended)

### Installation

#### From GitHub release

1. Download the latest `.zip` from [this direct link](https://github.com/fernandotellado/eu-withdrawal-compliance/archive/refs/heads/main.zip).
2. In your WordPress admin, go to **Plugins → Add New → Upload Plugin**.
3. Upload the zip and activate.

#### From source

```bash
git clone https://github.com/fernandotellado/eu-withdrawal-compliance.git
```

Move the folder to `wp-content/plugins/` and activate from the WordPress admin.

### Configuration

After activation:

1. The plugin creates a "Right of withdrawal" page automatically with a sample legal template. Review and edit it from **Pages**.
2. Go to **WooCommerce → EU Withdrawal** (or **Withdrawals → Settings** without WooCommerce) to configure the notification email and the page that hosts the form.
3. Add the URL of the withdrawal page to your footer or legal links so it is visible from any page on your site.

### Filters and hooks

| Filter | Purpose |
|--------|---------|
| `ayudawp_euw_grace_days` | Number of additional days to add to the 14-day deadline check. Default: 0. |
| `ayudawp_euw_skip_deadline_check` | Return `true` to disable the 14-day check entirely. Default: `false`. |
| `ayudawp_euw_email_ids` | Array of WooCommerce email IDs where the withdrawal notice is injected. Default: `customer_processing_order`, `customer_on_hold_order`, `customer_completed_order`. |

| Action | Purpose |
|--------|---------|
| `ayudawp_euw_after_submission` | Fires after a withdrawal request has been processed. Receives the CPT ID and the submission data array. |

### Compliance status

This plugin implements the **minimum compliant version** of EU Directive 2023/2673. It works on all member states from 19 June 2026.

The German interpretation of the directive (the strictest known so far) requires a two-step confirmation flow: a first button that opens the function, an intermediate page with the customer's data, and a second "confirm withdrawal" button that submits the request. This is not yet implemented because Spanish transposition is still pending as of May 2026 and a future update may be required to align with the final Spanish Real Decreto.

### License

GPL-2.0-or-later. See [LICENSE](LICENSE).

### Author

Created and maintained by Fernando Tellado at [AyudaWP.com](https://ayudawp.com).

For installation, configuration or custom development services, see [mantenimiento.ayudawp.com](https://mantenimiento.ayudawp.com).

### Contributing

Bug reports, pull requests and translations are welcome. Open an issue first to discuss any non-trivial change.

---

## Español

Plugin de WordPress que añade la función online de desistimiento exigida por la Directiva (UE) 2023/2673, obligatoria desde el 19 de junio de 2026 para todo comercio online de la Unión Europea.

### Qué hace

- Crea automáticamente al activar una página pública de desistimiento con el formulario embebido mediante el shortcode `[ayudawp_withdrawal_form]`. La página viene pre-rellenada con una plantilla legal de ejemplo, neutra y multilingüe, con un aviso claro de «revísalo con tu asesor legal» para que la publiques tras una revisión rápida.
- Añade un endpoint «Derecho de desistimiento» dentro de **Mi cuenta** de WooCommerce, con un botón «Desistir» por pedido que solo aparece mientras la ventana de 14 días sigue abierta.
- Inyecta un aviso «Solicitar desistimiento aquí» con enlace al formulario en los emails transaccionales de WooCommerce (pedido recibido, en espera y completado), cumpliendo la obligación del comerciante de informar al consumidor sobre la existencia y ubicación de la función de desistimiento.
- Valida el par email/pedido contra la base de datos de WooCommerce, incluyendo la comprobación del plazo de 14 días.
- Añade una nota privada al pedido de WooCommerce enlazando la solicitud con el registro del log.
- Envía email de confirmación al cliente y email de notificación al admin de la tienda, con `reply-to` apuntando al cliente para responderle de un solo clic.
- Registra cada solicitud como un custom post type privado con seguimiento de estados (pendiente, aceptada, rechazada, completada), IP y user agent para trazabilidad legal.
- Se integra dentro del menú de administración de WooCommerce cuando WooCommerce está activo (los ajustes viven en **WooCommerce → EU Withdrawal**, el log de solicitudes en **WooCommerce → Withdrawals**). Si WooCommerce no está activo, cae a un menú de nivel superior propio.

### Requisitos

- WordPress 6.0 o superior
- PHP 7.4 o superior
- WooCommerce 7.0 o superior (recomendado)

### Instalación

#### Desde GitHub (Releases)

1. Descarga el `.zip` más reciente desde [este enlace directo](https://github.com/fernandotellado/eu-withdrawal-compliance/archive/refs/heads/main.zip).
2. En el panel de WordPress, ve a **Plugins → Añadir nuevo → Subir plugin**.
3. Sube el zip y activa.

#### Desde el código fuente

```bash
git clone https://github.com/fernandotellado/eu-withdrawal-compliance.git
```

Mueve la carpeta a `wp-content/plugins/` y activa desde el panel de WordPress.

### Configuración

Tras la activación:

1. El plugin crea automáticamente una página «Derecho de desistimiento» con una plantilla legal de ejemplo. Revísala y edítala desde **Páginas**.
2. Ve a **WooCommerce → EU Withdrawal** (o **Withdrawals → Settings** si no usas WooCommerce) para configurar el email de notificación y la página que aloja el formulario.
3. Añade la URL de la página de desistimiento al pie o a los enlaces legales para que sea visible desde cualquier página del sitio.

### Filtros y acciones

| Filtro | Para qué sirve |
|--------|---------|
| `ayudawp_euw_grace_days` | Días adicionales que se suman al plazo de 14 días. Por defecto: 0. |
| `ayudawp_euw_skip_deadline_check` | Devuelve `true` para desactivar completamente la comprobación de los 14 días. Por defecto: `false`. |
| `ayudawp_euw_email_ids` | Array de IDs de emails de WooCommerce donde se inyecta el aviso de desistimiento. Por defecto: `customer_processing_order`, `customer_on_hold_order`, `customer_completed_order`. |

| Acción | Para qué sirve |
|--------|---------|
| `ayudawp_euw_after_submission` | Se dispara tras procesar una solicitud de desistimiento. Recibe el ID del CPT y el array con los datos del envío. |

### Estado de cumplimiento

Este plugin implementa la **versión mínima conforme** con la Directiva (UE) 2023/2673. Es válida en todos los Estados miembros desde el 19 de junio de 2026.

La interpretación alemana de la directiva (la más estricta conocida hasta la fecha) exige un flujo de doble confirmación: un primer botón que abre la función, una página intermedia con los datos del cliente y un segundo botón «confirmar desistimiento» que envía la solicitud. Aún no está implementado porque la transposición española sigue pendiente a 1 de mayo de 2026, y es probable que se necesite una actualización futura para alinear el plugin con el Real Decreto definitivo.

### Licencia

GPL-2.0-or-later. Ver [LICENSE](LICENSE).

### Autor

Creado y mantenido por Fernando Tellado en [AyudaWP.com](https://ayudawp.com).

Para servicios de instalación, configuración o desarrollo a medida, ver [mantenimiento.ayudawp.com](https://mantenimiento.ayudawp.com).

### Contribuir

Se aceptan reportes de bugs, pull requests y traducciones. Abre un issue primero para discutir cualquier cambio no trivial.
