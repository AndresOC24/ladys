# Design System — Lady's On Go (MASTER)

> **LOGIC:** When building a specific page, first check `design-system/pages/[page-name].md`.
> If that file exists, its rules **override** this Master file.
> If not, strictly follow the rules below.
>
> Fuente de verdad global de UI/UX. Generado con la skill `ui-ux-pro-max` y sintetizado
> para el producto: sistema de verificación de identidad para transporte privado de mujeres.
> Toda vista nueva o modificada DEBE seguir estas reglas.

## Patrón de producto
- **Trust & Authority + Conversion**: la confianza es el mensaje central.
- Cada pantalla tiene UN solo CTA primario; acciones secundarias visualmente subordinadas.
- Flujos multi-paso siempre con indicador de progreso y navegación hacia atrás.

## Colores (tokens semánticos — Tailwind)
| Token | Valor | Uso |
|---|---|---|
| `primary` | `#EC4899` (pink-500, escala pink completa) | CTAs, links, foco, marca |
| `accent` | `#8B5CF6` (violet-500) | Gradientes con primary, detalles |
| `background` | `#FDF2F8` (pink-50) | Fondo de páginas |
| `surface` | `#FFFFFF` | Cards y formularios |
| `foreground` | `#831843` (pink-900) para títulos de marca, `slate-800` para texto | Texto |
| `muted` | `slate-500` | Texto secundario |
| `border` | `#FBCFE8` (pink-200) | Bordes suaves |
| `destructive` | `#DC2626` (red-600) | Errores y acciones destructivas |
| Estados | verde=éxito, amarillo=pendiente, azul=proceso, púrpura=revisión, rojo=rechazo | Siempre con ícono+texto, nunca solo color |

- Gradiente de marca: `from-pink-500 to-violet-500` (botones primarios, headers hero).

## Tipografía
- **Familia única:** Plus Jakarta Sans (Google Fonts), pesos 400/500/600/700/800.
- Body 16px mínimo, line-height 1.5–1.75. Títulos 600–800. Labels 500.

## Estilo visual
- Minimalismo suave: cards `rounded-2xl`, `shadow-sm`/`shadow-lg shadow-pink-100`,
  bordes `border-pink-100`, espaciado generoso (sistema 4/8).
- Inputs `rounded-xl`, foco `ring-pink-500`, altura táctil ≥44px.
- Transiciones 150–300ms, `ease-out` al entrar; respetar `prefers-reduced-motion`.

## Reglas duras (anti-patrones prohibidos)
1. **NO emojis como íconos** → SVG inline de Heroicons (outline, stroke 1.5).
2. NO hex sueltos en componentes → clases Tailwind de la paleta definida.
3. NO labels solo-placeholder → label visible por campo, error debajo del campo.
4. NO ocultar el progreso en flujos multi-paso.
5. NO estados transmitidos solo por color (siempre ícono + texto).
6. Botones con estado loading (deshabilitar + spinner) en operaciones async.
7. Touch targets ≥44px, `cursor-pointer` en clickeables, focus ring visible.

## Checklist pre-entrega (por vista)
- [ ] Contraste texto ≥4.5:1  [ ] Responsive 375/768/1024  [ ] Focus visible
- [ ] Un solo CTA primario    [ ] Íconos SVG consistentes  [ ] Feedback en submit
