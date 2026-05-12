# EU Withdrawal Compliance

WordPress plugin that adds the EU online withdrawal function required by Directive (EU) 2023/2673, mandatory from 19 June 2026 for every online retailer in the European Union.

🇬🇧 [English](#english) · 🇪🇸 [Español](#español)

> 📦 **The plugin is being submitted to [WordPress.org](https://wordpress.org/plugins/) as `eu-withdrawal-compliance` (version 1.4.0).**
> Once approved, install and update it from your WordPress admin → **Plugins → Add New** → search "EU Withdrawal Compliance".
> Sites that already have it installed under the correct slug (`wp-content/plugins/eu-withdrawal-compliance/`) will receive the 1.4.0 update automatically via the standard WordPress updater. This GitHub repo will become a read-only archive a few days after the wp.org release — feature work and bugfixes will live on wp.org from then on.
>
> 📦 **El plugin se está enviando a [WordPress.org](https://es.wordpress.org/plugins/) como `eu-withdrawal-compliance` (versión 1.4.0).**
> Cuando se apruebe, instálalo y actualízalo desde tu admin de WordPress → **Plugins → Añadir nuevo** → busca "EU Withdrawal Compliance".
> Las webs que ya lo tienen instalado con el slug correcto (`wp-content/plugins/eu-withdrawal-compliance/`) recibirán la 1.4.0 automáticamente desde el actualizador de WordPress. Este repo de GitHub pasará a archivo de solo lectura unos días después de la publicación en wp.org — a partir de ese momento las nuevas funcionalidades y correcciones de fallos viven en wp.org.

---

## English

### What it does

- Creates a public withdrawal page automatically on activation, with a fully-escaped form embedded via the `[ayudawp_withdrawal_form]` shortcode. The page is pre-filled with a neutral, multilingual sample template (with a clear "review with a legal advisor" disclaimer) so you can publish it after a quick review.
- Adds a "Right of withdrawal" endpoint inside WooCommerce **My Account** with a per-order "Withdraw" button shown only while the configured withdrawal window is open.
- Injects an "Exercise withdrawal right here" notice with link to the form inside WooCommerce transactional emails (processing, completed and customer invoice), to comply with the trader's obligation to inform consumers about the existence and placement of the withdrawal function. The list of eligible order statuses is configurable from the settings page (defaults to Processing and Completed).
- Validates the email/order pair against the WooCommerce database, including the deadline check.
- **Configurable deadline** (1.2.0+): choose whether the 14-day window starts from the order date or from the WooCommerce completion date, and add extra grace days from the settings page.
- **Article 16 exclusions** (1.2.0+): mark individual products or whole categories as excluded from the right of withdrawal (custom-made, perishable, sealed digital, etc.). Requests on orders containing excluded items are flagged for the admin to review manually — never auto-rejected, since a partial withdrawal over the rest of the order can still be valid.
- **Verifiable receipt hash** (1.2.0+): every submission generates a SHA-256 hash sent to the customer in the confirmation email so they keep a tamper-evident proof on a durable medium.
- Adds a private note to the WooCommerce order at every step of the lifecycle: when the request is received and again when it is accepted, rejected or marked as completed.
- Sends a confirmation email to the customer on submission **and** a follow-up email when the request is accepted, rejected or completed. Optional admin comment is forwarded to the customer (required for rejections, optional for completed). Notification email to the shop admin uses reply-to pointing to the customer.
- Bulk actions in the withdrawals listing to mark several requests as accepted, rejected or completed at once.
- "Withdrawal" column in the WooCommerce orders screen (legacy and HPOS) showing the status of any linked request, toggleable from "Screen Options".
- Logs every request as a private custom post type with status tracking (pending, accepted, rejected, completed), IP, user agent and submission timestamp for legal traceability.
- Integrates inside the WooCommerce admin menu when WooCommerce is active (settings live at **WooCommerce → EU Withdrawal**, request log at **WooCommerce → Withdrawals**). Falls back to a top-level menu when running standalone.

### Requirements

- WordPress 6.0 or higher
- PHP 7.4 or higher
- WooCommerce 7.0 or higher

### Installation

#### From GitHub release

1. Download the latest `eu-withdrawal-compliance.zip` from [the latest release](https://github.com/fernandotellado/eu-withdrawal-compliance/releases/latest).
2. In your WordPress admin, go to **Plugins → Add New → Upload Plugin**.
3. Upload the zip and activate.

> ⚠️ Do **not** use the green "Code → Download ZIP" button on the repo page. That zip extracts as `eu-withdrawal-compliance-main/` and would install the plugin under the wrong slug, breaking future updates from WordPress.org.

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
| `ayudawp_euw_grace_days` | Number of additional days to add to the 14-day deadline check. Default: the value stored in **WooCommerce → EU Withdrawal → Withdrawal deadline → Grace days** (`0` if unset). The filter receives that value, so `return $days + N;` adds on top of the configured grace. |
| `ayudawp_euw_skip_deadline_check` | Return `true` to disable the deadline check entirely. Receives the WC_Order as second argument. Default: `false`. |
| `ayudawp_euw_email_ids` | Array of WooCommerce email IDs where the withdrawal notice is injected. Default: `customer_processing_order`, `customer_completed_order`, `customer_invoice`. |
| `ayudawp_euw_allowed_statuses` | Array of WooCommerce order statuses (without the `wc-` prefix) for which the withdrawal button and email notice are offered. Default: the value stored in **WooCommerce → EU Withdrawal → Eligible order statuses** (`processing`, `completed` if unset). Receives the value and the current `WC_Order` (when available). |

| Action | Purpose |
|--------|---------|
| `ayudawp_euw_after_submission` | Fires after a withdrawal request has been processed. Receives the CPT ID and the submission data array. |
| `ayudawp_euw_after_status_change` | Fires after a withdrawal request changes status (individually or via bulk action). Receives the CPT ID, the new status and the optional admin comment. |

The receipt hash helper `ayudawp_euw_compute_receipt_hash( $post_id, $name, $email, $order, $scope, $date, $submitted_at )` is also available for recomputing the SHA-256 of a request from its stored meta fields when verifying a customer's saved confirmation email.

### Migrating from the old "Download ZIP" install

If you installed an early version of this plugin by clicking the green **Code → Download ZIP** button on GitHub, your install lives in `wp-content/plugins/eu-withdrawal-compliance-main/`. That is the wrong slug and it will block automatic updates from WordPress.org. Migrate like this without losing any data:

1. **Deactivate** the existing plugin (the one labeled with the `-main` suffix). Do **not** click "Delete" yet.
2. Upload the new `eu-withdrawal-compliance.zip` from the [latest release](https://github.com/fernandotellado/eu-withdrawal-compliance/releases/latest) via **Plugins → Add New → Upload Plugin**.
3. **Activate** the new plugin. It will reuse the existing settings, the existing withdrawal page and the existing log of requests automatically.
4. Now go back to **Plugins**, find the old `-main` entry and click **Delete**. The plugin's uninstall script detects the canonical install next to it and keeps every option intact.

### Compliance status

This plugin implements the **minimum compliant version** of EU Directive 2023/2673. It works on all member states from 19 June 2026.

The German interpretation of the directive (the strictest known so far) requires a two-step confirmation flow: a first button that opens the function, an intermediate page with the customer's data, and a second "confirm withdrawal" button that submits the request. This is not yet implemented because Spanish transposition is still pending as of May 2026 and a future update may be required to align with the final Spanish Real Decreto.

### Changelog

**1.3.0**
- New: configurable list of order statuses for which the withdrawal button and email notice are offered. All WooCommerce-registered statuses appear in the new **WooCommerce → EU Withdrawal → Eligible order statuses** section (custom statuses from shipping or fulfilment plugins included). Default: Processing and Completed.
- New: the notice is now also injected in the **Customer invoice / Order details** email (the manually triggered one). It only renders when the order status matches the configured list, so the manual invoice can still be sent on any status without leaking the notice.
- Change: the notice is no longer shipped in the **On-hold** email by default. On-hold typically means payment is still pending, so offering the right of withdrawal at that point would be misleading. Sites that want the previous behaviour can opt back in via the `ayudawp_euw_email_ids` filter.
- New filter `ayudawp_euw_allowed_statuses` to override the eligible statuses programmatically (receives the array and the current `WC_Order` when available).
- Internal: shared `ayudawp_euw_should_show_withdrawal( $order )` helper that combines deadline + status check; reused by the My Account action and the email notice.
- Dev: PHP_CodeSniffer ruleset (`phpcs.xml.dist`), `composer.json` with WPCS and PHPCompatibilityWP dev deps, and a GitHub Actions workflow to run PHPCS on push and PRs. Thanks to [@webdados](https://github.com/webdados) for the suggestions on #3, #4 and #5.

**1.2.2**
- Fix: the form now resolves the order by its displayed number, not just by the internal post ID. Compatible with WooCommerce Sequential Order Numbers, Sequential Order Numbers Pro, Custom Order Numbers for WooCommerce (WPFactory) and any plugin that stores the customer-facing number in the standard `_order_number` post meta.
- The "Withdraw" button on the WooCommerce **My Account → Orders** screen and the link injected into WooCommerce transactional emails now pre-fill the form with the same order number the customer sees in their receipt.
- New filter `ayudawp_euw_pre_resolve_wc_order` to short-circuit the resolver for plugins with non-meta numbering schemes (e.g. YITH Sequential Order Number) and `ayudawp_euw_resolve_wc_order` for late override or auditing.

**1.2.1**
- Fix: validate that the WooCommerce order exists when WC is active. The previous fallback accepted submissions whose order number could not be matched against a real WC order — intended as an escape hatch for non-WC purchases — which let users submit withdrawals with completely invented order numbers. Sites that genuinely accept non-WC purchases can opt back into the lenient behaviour with the new `ayudawp_euw_allow_unverified_order` filter.
- Fix: translate the Scope value (Full/Partial) in the withdrawal detail metabox. It used to render the raw stored value in English even on translated sites.

**1.2.0**
- Article 16 exclusions: mark individual products or whole WooCommerce categories as excluded from the right of withdrawal. Subcategories inherit the exclusion from the parent automatically. Requests on orders containing excluded items are flagged for manual review (never auto-rejected) so a partial withdrawal over the rest of the order can still be valid.
- Instant-search picker for excluded categories in settings, with removable chips and instant auto-save (no need to hit "Save changes" for each chip).
- Inherited exclusion is reflected in the product editor: the per-product checkbox renders ticked and disabled, with a note pointing to the category responsible for the inheritance.
- Verifiable SHA-256 receipt hash. Every submission emails the customer a tamper-evident code as proof on a durable medium; recomputable from the stored fields.
- Configurable withdrawal deadline: choose order date vs. completion date as the basis, and add extra grace days from the settings page. The `ayudawp_euw_grace_days` filter still works on top of the stored value.
- Submission timestamp (UTC) and receipt hash shown on the request detail screen.
- Polished CPT labels ("Edit withdrawal", "New withdrawal", etc.).
- Internal: split `functions-admin.php` into `admin/columns.php`, `admin/metaboxes.php` and `admin/bulk-actions.php`. No behavioural change.
- Updated Spanish (es_ES) translation with every new string.

**1.1.0**
- Customer email notifications on every status change (accepted, rejected, completed).
- Optional admin comment forwarded to the customer on status change. Required for rejections, optional for completed requests.
- WooCommerce order notes on every status change so the order timeline reflects the full withdrawal lifecycle.
- Bulk actions in the withdrawals listing to mark several requests as accepted, rejected or completed at once.
- "Withdrawal" column in the WooCommerce orders screen (legacy and HPOS) showing the status of any linked request, toggleable from "Screen Options".
- Trimmed inline styles in the WooCommerce email notice so it inherits the email template styles instead of forcing a coloured callout box.

**1.0.0**
- Initial release.

### License

GPL-2.0-or-later. See [LICENSE](LICENSE).

### Author

Created and maintained by Fernando Tellado at [AyudaWP.com](https://ayudawp.com).

For installation, configuration or custom development services, see [mantenimiento.ayudawp.com](https://mantenimiento.ayudawp.com).

### Feedback

This plugin is about to be submitted to the [WordPress.org plugin directory](https://wordpress.org/plugins/), and once it lives there its public support channel will be the wp.org support forum. I'd rather not run a parallel issue/PR process here on GitHub — keeping both in sync would only make sense if the project were going to live on GitHub long-term, and it isn't. Apologies for the inconvenience if you came here to open an issue or a pull request.

If you'd like to share feedback, report a bug, suggest a feature or send a fix, please email me at [github@ayudawp.com](mailto:github@ayudawp.com) — I read and reply to every message. Translations are very welcome too; send the `.po` file the same way. Thanks for understanding.

---

## Español

Plugin de WordPress que añade la función online de desistimiento exigida por la Directiva (UE) 2023/2673, obligatoria desde el 19 de junio de 2026 para todo comercio online de la Unión Europea.

### Qué hace

- Crea automáticamente al activar una página pública de desistimiento con el formulario embebido mediante el shortcode `[ayudawp_withdrawal_form]`. La página viene pre-rellenada con una plantilla legal de ejemplo, neutra y multilingüe, con un aviso claro de «revísalo con tu asesor legal» para que la publiques tras una revisión rápida.
- Añade un endpoint «Derecho de desistimiento» dentro de **Mi cuenta** de WooCommerce, con un botón «Desistir» por pedido que solo aparece mientras la ventana configurada sigue abierta.
- Inyecta un aviso «Solicitar desistimiento aquí» con enlace al formulario en los emails transaccionales de WooCommerce (procesando, completado y factura del cliente), cumpliendo la obligación del comerciante de informar al consumidor sobre la existencia y ubicación de la función de desistimiento. La lista de estados de pedido elegibles es configurable desde la página de ajustes (por defecto: Procesando y Completado).
- Valida el par email/pedido contra la base de datos de WooCommerce, incluyendo la comprobación del plazo.
- **Plazo configurable** (1.2.0+): elige si la ventana de 14 días empieza desde la fecha del pedido o desde la fecha de completado de WooCommerce, y suma días de cortesía adicionales desde la página de ajustes.
- **Exclusiones del Artículo 16** (1.2.0+): marca productos individuales o categorías enteras como excluidos del derecho de desistimiento (a medida, perecederos, contenido digital sellado, etc.). Las solicitudes sobre pedidos con productos excluidos quedan marcadas para revisión manual del admin — nunca se rechazan automáticamente, porque un desistimiento parcial sobre el resto del pedido puede seguir siendo válido.
- **Hash de acuse verificable** (1.2.0+): cada solicitud genera un SHA-256 que se envía al cliente en el email de confirmación como prueba inmutable en soporte duradero.
- Añade notas privadas al pedido de WooCommerce en cada paso del ciclo de vida: cuando se recibe la solicitud y de nuevo cuando se acepta, se rechaza o se marca como completada.
- Envía email de confirmación al cliente al recibir la solicitud **y además** un email de seguimiento cuando se acepta, se rechaza o se marca como completada. Se puede incluir un comentario opcional del administrador en el email al cliente (obligatorio en rechazos, opcional en completadas). El email al admin lleva `reply-to` apuntando al cliente.
- Acciones en lote en el listado de solicitudes para marcar varias como aceptadas, rechazadas o completadas a la vez.
- Columna «Desistimiento» en la pantalla de pedidos de WooCommerce (legacy y HPOS) que muestra el estado de la solicitud asociada al pedido. Activable/desactivable desde «Opciones de pantalla».
- Registra cada solicitud como un custom post type privado con seguimiento de estados (pendiente, aceptada, rechazada, completada), IP, user agent y timestamp de envío para trazabilidad legal.
- Se integra dentro del menú de administración de WooCommerce cuando WooCommerce está activo (los ajustes viven en **WooCommerce → EU Withdrawal**, el log de solicitudes en **WooCommerce → Withdrawals**). Si WooCommerce no está activo, cae a un menú de nivel superior propio.

### Requisitos

- WordPress 6.0 o superior
- PHP 7.4 o superior
- WooCommerce 7.0 o superior

### Instalación

#### Desde GitHub (Releases)

1. Descarga el archivo `eu-withdrawal-compliance.zip` desde [la última release](https://github.com/fernandotellado/eu-withdrawal-compliance/releases/latest).
2. En el panel de WordPress, ve a **Plugins → Añadir nuevo → Subir plugin**.
3. Sube el zip y activa.

> ⚠️ **No uses** el botón verde "Code → Download ZIP" del repo. Ese zip se descomprime como `eu-withdrawal-compliance-main/` e instalaría el plugin con un slug incorrecto, rompiendo las actualizaciones futuras desde WordPress.org.

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
| `ayudawp_euw_grace_days` | Días adicionales que se suman al plazo de 14 días. Por defecto: el valor guardado en **WooCommerce → EU Withdrawal → Withdrawal deadline → Grace days** (`0` si no está definido). El filtro recibe ese valor, así que `return $días + N;` suma encima de la cortesía configurada. |
| `ayudawp_euw_skip_deadline_check` | Devuelve `true` para desactivar completamente la comprobación del plazo. Recibe el WC_Order como segundo argumento. Por defecto: `false`. |
| `ayudawp_euw_email_ids` | Array de IDs de emails de WooCommerce donde se inyecta el aviso de desistimiento. Por defecto: `customer_processing_order`, `customer_completed_order`, `customer_invoice`. |
| `ayudawp_euw_allowed_statuses` | Array de estados de pedido de WooCommerce (sin el prefijo `wc-`) para los que se ofrece el botón de «Mi cuenta» y el aviso en email. Por defecto: el valor guardado en **WooCommerce → EU Withdrawal → Eligible order statuses** (`processing`, `completed` si no está definido). Recibe el valor y el `WC_Order` actual (cuando está disponible). |

| Acción | Para qué sirve |
|--------|---------|
| `ayudawp_euw_after_submission` | Se dispara tras procesar una solicitud de desistimiento. Recibe el ID del CPT y el array con los datos del envío. |
| `ayudawp_euw_after_status_change` | Se dispara tras un cambio de estado en una solicitud (individual o en lote). Recibe el ID del CPT, el nuevo estado y el comentario opcional del administrador. |

La función helper `ayudawp_euw_compute_receipt_hash( $post_id, $name, $email, $order, $scope, $date, $submitted_at )` también está disponible para recalcular el SHA-256 de una solicitud desde sus metas guardadas cuando se quiera verificar el email de confirmación que conserva el cliente.

### Migrar desde la instalación antigua de "Download ZIP"

Si instalaste una primera versión del plugin pulsando el botón verde **Code → Download ZIP** de GitHub, tu instalación vive en `wp-content/plugins/eu-withdrawal-compliance-main/`. Ese slug es incorrecto y bloquea las actualizaciones automáticas desde WordPress.org. Migra así, sin perder ningún dato:

1. **Desactiva** el plugin existente (el que termina en `-main`). **No** pulses "Eliminar" todavía.
2. Sube el nuevo `eu-withdrawal-compliance.zip` desde la [última release](https://github.com/fernandotellado/eu-withdrawal-compliance/releases/latest) en **Plugins → Añadir nuevo → Subir plugin**.
3. **Activa** el plugin nuevo. Reutilizará automáticamente los ajustes, la página de desistimiento existente y el log de solicitudes.
4. Vuelve a **Plugins**, busca la entrada con sufijo `-main` y pulsa **Eliminar**. El script de desinstalación detecta que existe la instalación canónica al lado y conserva todas las opciones intactas.

### Estado de cumplimiento

Este plugin implementa la **versión mínima conforme** con la Directiva (UE) 2023/2673. Es válida en todos los Estados miembros desde el 19 de junio de 2026.

La interpretación alemana de la directiva (la más estricta conocida hasta la fecha) exige un flujo de doble confirmación: un primer botón que abre la función, una página intermedia con los datos del cliente y un segundo botón «confirmar desistimiento» que envía la solicitud. Aún no está implementado porque la transposición española sigue pendiente a 1 de mayo de 2026, y es probable que se necesite una actualización futura para alinear el plugin con el Real Decreto definitivo.

### Registro de cambios

**1.3.0**
- Nuevo: lista configurable de estados de pedido para los que se ofrece el botón de desistimiento y el aviso en emails. Aparecen todos los estados registrados en WooCommerce en la nueva sección **WooCommerce → EU Withdrawal → Eligible order statuses** (incluidos los estados personalizados de plugins de envío o fulfillment). Por defecto: Procesando y Completado.
- Nuevo: el aviso se inyecta también en el email **Factura del cliente / Detalles del pedido** (el que se dispara manualmente). Solo aparece cuando el estado del pedido coincide con la lista configurada, así que se puede seguir enviando la factura manual en cualquier estado sin que aparezca el aviso fuera de lugar.
- Cambio: el aviso ya no se incluye por defecto en el email **En espera**. «En espera» suele significar que el pago aún no se ha confirmado, así que ofrecer el desistimiento ahí induce a confusión. Quien quiera recuperar el comportamiento anterior puede hacerlo con el filtro `ayudawp_euw_email_ids`.
- Nuevo filtro `ayudawp_euw_allowed_statuses` para sobrescribir programáticamente los estados elegibles (recibe el array y el `WC_Order` actual cuando está disponible).
- Interno: nueva función compartida `ayudawp_euw_should_show_withdrawal( $order )` que combina deadline + estado; la reutilizan el botón de «Mi cuenta» y el inyector de emails.
- Desarrollo: ruleset de PHP_CodeSniffer (`phpcs.xml.dist`), `composer.json` con WPCS y PHPCompatibilityWP como deps de desarrollo y workflow de GitHub Actions que pasa PHPCS en push y PRs. Gracias a [@webdados](https://github.com/webdados) por las sugerencias en #3, #4 y #5.

**1.2.2**
- Corrección: el formulario ahora localiza el pedido por su número visible, no solo por el ID interno del post. Compatible con WooCommerce Sequential Order Numbers, Sequential Order Numbers Pro, Custom Order Numbers for WooCommerce (WPFactory) y cualquier plugin que guarde el número que ve el cliente en la meta estándar `_order_number`.
- El botón «Desistir» de **Mi cuenta → Pedidos** y el enlace que se añade a los correos transaccionales de WooCommerce ahora prerellenan el formulario con el mismo número de pedido que el cliente ve en su recibo.
- Nuevo filtro `ayudawp_euw_pre_resolve_wc_order` para hacer cortocircuito en el resolver con plugins que usan esquemas de numeración fuera de la meta estándar (por ejemplo YITH Sequential Order Number) y `ayudawp_euw_resolve_wc_order` para sobrescribir o auditar el resultado final.

**1.2.1**
- Corrección: valida que el pedido de WooCommerce exista cuando WC está activo. El fallback anterior aceptaba envíos cuyo número de pedido no coincidía con ningún pedido real de WC — pensado como vía de escape para compras fuera de WooCommerce — y permitía enviar desistimientos con números de pedido completamente inventados. Las tiendas que sí acepten compras no-WC pueden recuperar el comportamiento permisivo con el nuevo filtro `ayudawp_euw_allow_unverified_order`.
- Corrección: traduce el valor del alcance (Completo/Parcial) en la metabox de detalle del desistimiento. Antes mostraba el valor guardado en bruto en inglés incluso en sitios traducidos.

**1.2.0**
- Exclusiones del Artículo 16: marca productos individuales o categorías enteras de WooCommerce como excluidos del derecho de desistimiento. Las subcategorías heredan automáticamente la exclusión de la categoría padre. Las solicitudes sobre pedidos con artículos excluidos se marcan para revisión manual (nunca se rechazan automáticamente), porque un desistimiento parcial sobre el resto del pedido puede seguir siendo válido.
- Buscador instant de categorías excluidas en los ajustes, con chips eliminables y autoguardado al instante (sin pulsar «Guardar cambios» en cada chip).
- La exclusión heredada se refleja en el editor de producto: la casilla individual aparece marcada y deshabilitada, con una nota indicando la categoría que la hereda.
- Hash SHA-256 de acuse verificable. Cada solicitud envía al cliente un código como prueba inmutable en soporte duradero, recalculable desde los campos guardados.
- Plazo de desistimiento configurable: elige fecha del pedido vs. fecha de completado como base y suma días de cortesía desde la página de ajustes. El filtro `ayudawp_euw_grace_days` sigue funcionando encima del valor guardado.
- Timestamp de envío (UTC) y hash del acuse visibles en el detalle de cada solicitud.
- Etiquetas del CPT pulidas («Editar desistimiento», «Nuevo desistimiento», etc.).
- Interno: `functions-admin.php` se ha dividido en `admin/columns.php`, `admin/metaboxes.php` y `admin/bulk-actions.php`. Sin cambios de comportamiento.
- Traducción al español (es_ES) actualizada con todas las cadenas nuevas.

**1.1.0**
- Emails al cliente en cada cambio de estado (aceptada, rechazada, completada).
- Comentario opcional del administrador que se envía al cliente en el email de cambio de estado. Obligatorio en rechazos, opcional en completadas.
- Notas en el pedido de WooCommerce en cada cambio de estado, para que la línea temporal del pedido refleje todo el ciclo de vida del desistimiento.
- Acciones en lote en el listado de solicitudes para marcar varias como aceptadas, rechazadas o completadas a la vez.
- Columna «Desistimiento» en la pantalla de pedidos de WooCommerce (legacy y HPOS) que muestra el estado de la solicitud asociada al pedido. Activable/desactivable desde «Opciones de pantalla».
- Reducidos los estilos inline del aviso en el email de WooCommerce para que herede los estilos de la plantilla del email en lugar de forzar una caja con colores propios.

**1.0.0**
- Versión inicial.

### Licencia

GPL-2.0-or-later. Ver [LICENSE](LICENSE).

### Autor

Creado y mantenido por Fernando Tellado en [AyudaWP.com](https://ayudawp.com).

Para servicios de instalación, configuración o desarrollo a medida, ver [mantenimiento.ayudawp.com](https://mantenimiento.ayudawp.com).

### Feedback

El plugin está a punto de subirse al [directorio de plugins de WordPress.org](https://wordpress.org/plugins/) y, una vez allí, el canal público de soporte será el foro del propio directorio. Prefiero no mantener en paralelo un proceso de issues y pull requests en GitHub: tenerlos sincronizados solo tendría sentido si el proyecto fuese a vivir aquí a largo plazo, y no es el caso. Disculpa las molestias si venías a abrir una issue o un PR.

Para enviarme feedback, reportar un bug, sugerir una mejora o mandarme un parche, escríbeme a [github@ayudawp.com](mailto:github@ayudawp.com): leo y respondo todos los mensajes. Las traducciones también son muy bienvenidas; mándame el archivo `.po` por la misma vía. Gracias por entenderlo.
