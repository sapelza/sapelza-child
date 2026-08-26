<?php
/**
 * 04 Die Tour — neun Orte, eine Tour, Ihr Tag.
 *
 * Ein invertiertes Kapitel: dunkler Grund mitten in der hellen Seite. Das
 * ist der Rhythmuswechsel, nicht Dekoration — ohne ihn liest sich die
 * Seite als eine lange Fläche.
 *
 * Die Karte ist eine gesetzte Zeichnung, keine Geodaten-Ausgabe. Die
 * Beschriftungen sind von Hand platziert (an/oben), weil eine gerechnete
 * Lage im Talschluss immer irgendwo kollidiert.
 *
 * Toblach liegt im Nullpunkt: von dort fährt der Wagen los.
 */

if (!defined('ABSPATH')) exit;

/* Zeichenfeld 1000 × 300. Es wird gedehnt, die Striche nicht. */
$sz_x = static fn(float $x): float => round(44 + (($x + 1.24) / 2.4) * 912, 1);
$sz_y = static fn(float $y): float => round(150 + $y * 92, 1);

/* Talachse des Pustertals, West nach Ost. */
$sz_achse = [[-1.24, -0.26], [-1.02, -0.20], [-0.46, -0.06], [0, 0], [0.52, 0.02], [0.86, 0.08], [1.16, 0.16]];

/* Rienz nach Westen, Drau nach Osten — die Wasserscheide liegt im Toblacher Feld. */
$sz_fluesse = [
    [[0.04, 0.06], [-0.34, 0.13], [-0.72, 0.09], [-1.1, 0.02]],
    [[0.06, 0.06], [0.44, 0.12], [0.82, 0.15], [1.12, 0.24]],
];

$sz_orte = [
    ['n' => 'Welsberg',   'x' => -1.02, 'y' => -0.20, 'tal' => true,  'an' => 'end',    'oben' => true],
    ['n' => 'Taisten',    'x' => -1.14, 'y' => -0.48, 'von' => [-1.02, -0.20], 'an' => 'end',    'oben' => true],
    ['n' => 'Gsies',      'x' => -0.84, 'y' => -0.94, 'von' => [-1.02, -0.20], 'an' => 'middle', 'oben' => true],
    ['n' => 'Niederdorf', 'x' => -0.46, 'y' => -0.06, 'tal' => true,  'an' => 'end'],
    ['n' => 'Prags',      'x' => -0.78, 'y' => 0.50,  'von' => [-0.60, -0.03], 'an' => 'end'],
    ['n' => 'Innichen',   'x' => 0.52,  'y' => 0.02,  'tal' => true,  'an' => 'middle'],
    ['n' => 'Vierschach', 'x' => 0.86,  'y' => 0.08,  'tal' => true,  'an' => 'middle', 'oben' => true],
    ['n' => 'Winnebach',  'x' => 1.16,  'y' => 0.16,  'tal' => true,  'an' => 'start'],
    ['n' => 'Sexten',     'x' => 0.86,  'y' => 0.56,  'von' => [0.52, 0.02],  'an' => 'start'],
];

$sz_zusagen = [
    [
        't' => __('Kein Paketdienst', 'sapelza-shop'),
        'b' => __('Wir übergeben Ihre Ware an niemanden. Sie verlässt unser Lager in Toblach und kommt bei Ihnen an.', 'sapelza-shop'),
    ],
    [
        't' => __('Sie bestimmen den Tag', 'sapelza-shop'),
        'b' => __('Den Liefertag wählen Sie beim Bestellen — wenn der Betrieb offen ist und jemand Zeit hat, die Ware anzunehmen.', 'sapelza-shop'),
    ],
    [
        't' => __('Abgeladen, wo es gebraucht wird', 'sapelza-shop'),
        'b' => __('In die Küche, in den Keller, auf die Baustelle. Nicht an die Rampe und nicht vor das Tor.', 'sapelza-shop'),
    ],
];

/* Die Talachse als Linienzug. */
$sz_achse_d = '';
foreach ($sz_achse as $sz_i => $sz_p) {
    $sz_achse_d .= ($sz_i === 0 ? 'M' : 'L') . $sz_x($sz_p[0]) . ' ' . $sz_y($sz_p[1]) . ' ';
}
$sz_achse_d = trim($sz_achse_d);

?>
<section class="sz-kapitel sz-tour" aria-labelledby="sz-tour-titel">
    <div class="wrap" style="position: relative; z-index: 1;">

        <p class="sz-kapitelmarke sz-kapitelmarke--hell">
            <span class="sz-kapitelmarke__nr mono">04</span>
            <span class="hairline" aria-hidden="true"></span>
            <span class="sz-kapitelmarke__kicker mono"><?php echo esc_html__('Zustellung', 'sapelza-shop'); ?></span>
        </p>

        <div class="sz-tour__kopf">
            <h2 id="sz-tour-titel" class="sz-tour__titel">
                <?php echo esc_html__('Neun Orte, eine Tour, Ihr Tag', 'sapelza-shop'); ?>
            </h2>
            <p class="sz-tour__lead">
                <?php echo esc_html__('Unser Piaggio Porter fährt das Tal von Welsberg bis Winnebach, dazu Gsies, Prags und Sexten. Ihre Bestellung fährt mit — kein Paketdienst, keine Sendungsnummer, kein Zeitfenster, das jemand anderes festgelegt hat.', 'sapelza-shop'); ?>
            </p>
        </div>

        <div class="sz-tour__karte" data-sz-tour>
            <svg viewBox="0 0 1000 300" class="sz-tour__svg" role="img"
                 aria-label="<?php echo esc_attr__('Karte des Hochpustertals mit den neun belieferten Orten', 'sapelza-shop'); ?>">

                <?php /* Flüsse zuerst: sie liegen unter allem. */ ?>
                <?php foreach ($sz_fluesse as $sz_fluss) : ?>
                    <?php
                    $sz_d = '';
                    foreach ($sz_fluss as $sz_i => $sz_p) {
                        $sz_d .= ($sz_i === 0 ? 'M' : 'L') . $sz_x($sz_p[0]) . ' ' . $sz_y($sz_p[1]) . ' ';
                    }
                    ?>
                    <path class="sz-tour__fluss" d="<?php echo esc_attr(trim($sz_d)); ?>" />
                <?php endforeach; ?>

                <?php /* Stichstraßen zu den Seitentälern. */ ?>
                <?php foreach ($sz_orte as $sz_o) : ?>
                    <?php if (empty($sz_o['von'])) continue; ?>
                    <path class="sz-tour__stich"
                          d="M<?php echo esc_attr((string) $sz_x((float) $sz_o['von'][0])); ?> <?php echo esc_attr((string) $sz_y((float) $sz_o['von'][1])); ?> L<?php echo esc_attr((string) $sz_x((float) $sz_o['x'])); ?> <?php echo esc_attr((string) $sz_y((float) $sz_o['y'])); ?>" />
                <?php endforeach; ?>

                <?php /* Die Talachse: gestrichelt der ganze Weg, darüber der befahrene Teil. */ ?>
                <path class="sz-tour__achse" d="<?php echo esc_attr($sz_achse_d); ?>" />
                <path class="sz-tour__befahren" d="<?php echo esc_attr($sz_achse_d); ?>" data-sz-befahren />

                <?php /* Toblach — von hier fährt der Wagen los. */ ?>
                <g class="sz-tour__lager">
                    <circle cx="<?php echo esc_attr((string) $sz_x(0)); ?>" cy="<?php echo esc_attr((string) $sz_y(0)); ?>" r="7" />
                    <text x="<?php echo esc_attr((string) $sz_x(0)); ?>" y="<?php echo esc_attr((string) ($sz_y(0) + 26)); ?>" text-anchor="middle">
                        <?php echo esc_html__('Toblach · Lager', 'sapelza-shop'); ?>
                    </text>
                </g>

                <?php foreach ($sz_orte as $sz_o) : ?>
                    <?php
                    $sz_px = $sz_x((float) $sz_o['x']);
                    $sz_py = $sz_y((float) $sz_o['y']);
                    /* Beschriftung über oder unter dem Punkt — von Hand gesetzt,
                       weil eine gerechnete Lage im Talschluss kollidiert. */
                    $sz_ty = !empty($sz_o['oben']) ? $sz_py - 14 : $sz_py + 22;
                    ?>
                    <g class="sz-tour__ort">
                        <circle cx="<?php echo esc_attr((string) $sz_px); ?>" cy="<?php echo esc_attr((string) $sz_py); ?>" r="4" />
                        <text x="<?php echo esc_attr((string) $sz_px); ?>" y="<?php echo esc_attr((string) $sz_ty); ?>"
                              text-anchor="<?php echo esc_attr($sz_o['an']); ?>">
                            <?php echo esc_html($sz_o['n']); ?>
                        </text>
                    </g>
                <?php endforeach; ?>
            </svg>

            <?php /* Der Wagen liegt über der Karte und wird per Skript bewegt. */ ?>
            <img class="sz-tour__porter" data-sz-porter
                 src="<?php echo esc_url(get_stylesheet_directory_uri() . '/bilder/porter-seite.webp'); ?>"
                 width="1200" height="688" alt="" aria-hidden="true" loading="lazy" decoding="async">
        </div>

        <ul class="sz-tour__zusagen">
            <?php foreach ($sz_zusagen as $sz_i => $sz_z) : ?>
                <li class="sz-zusage" data-sz-zusage="<?php echo esc_attr((string) $sz_i); ?>">
                    <span class="sz-zusage__nr mono"><?php echo esc_html(str_pad((string) ($sz_i + 1), 2, '0', STR_PAD_LEFT)); ?></span>
                    <h3 class="sz-zusage__titel display"><?php echo esc_html($sz_z['t']); ?></h3>
                    <p class="sz-zusage__text"><?php echo esc_html($sz_z['b']); ?></p>
                </li>
            <?php endforeach; ?>
        </ul>

    </div>
</section>
