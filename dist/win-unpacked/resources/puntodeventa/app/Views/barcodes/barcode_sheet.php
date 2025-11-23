<?php
/**
 * @var array $barcode_config
 * @var array $items
 */

use App\Libraries\Barcode_lib;

$barcode_lib = new Barcode_lib();
?>

<!doctype html>
<html lang="<?= current_language_code() ?>">

<head>
    <meta charset="utf-8">
    <title><?= lang('Items.generate_barcodes') ?></title>
    <link rel="stylesheet" href="<?= base_url() ?>css/barcode_font.css">
    <style>
    @page {
        size: A4;
        margin: 10mm;
    }

    body {
        font-size: <?=$barcode_config['barcode_font_size'] ?>px;
        margin: 0;
    }

    table {
        width: 100%;
        border-collapse: collapse;
    }

    td {
        padding: <?=$barcode_config['barcode_page_cellspacing'] ?>px;
        text-align: center;
        vertical-align: top;
        width: <?=intval(100 / $barcode_config['barcode_num_in_row']) ?>%;
    }

    .barcode {
        text-align: center;
        margin-bottom: 5px;
    }

    .barcode svg {
        height: <?=$barcode_config['barcode_height'] ?>px;
        width: <?=$barcode_config['barcode_width'] ?>px;
    }

    .item-name,
    .item-price {
        font-size: <?=max(8, $barcode_config['barcode_font_size'] - 2) ?>px;
        margin: 2px 0;
    }

    td button#downloadAll {
        margin-bottom: 10px;
        padding: 5px 10px;
        font-size: 14px;
    }
    </style>
</head>

<body class="<?= 'font_' . $barcode_lib->get_font_name($barcode_config['barcode_font']) ?>">

    <!-- Botón único para descargar todos los códigos -->
    <button id="downloadAll">Descargar todos los códigos SVG</button>

    <table>
        <tr>
            <?php
            $count = 0;
            foreach ($items as $item) {
                if ($count % $barcode_config['barcode_num_in_row'] == 0 && $count != 0) {
                    echo '</tr><tr>';
                }

                $barcode_html = $barcode_lib->display_barcode($item, $barcode_config);
                $name = htmlspecialchars($item['name'] ?? 'Producto sin nombre');
                $price = number_format($item['unit_price'] ?? 0, 2);

                // Contenedor exportable que incluye solo lo necesario
                echo '<td>
                        <div class="exportable">
                            <div class="barcode" id="barcode_'.$count.'" data-name="'.$name.'">' . $barcode_html . '</div>
                            <div class="item-name">' . $name . '</div>
                            <div class="item-price">$ ' . $price . '</div>
                        </div>
                      </td>';

                $count++;
            }
            ?>
        </tr>
    </table>

    <!-- JSZip -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>

    <script>
    document.getElementById('downloadAll').addEventListener('click', async () => {
        const zip = new JSZip();
        const items = document.querySelectorAll('.exportable');

        items.forEach((div, index) => {
            // Crear un nuevo SVG contenedor
            const svgWrapper = document.createElementNS("http://www.w3.org/2000/svg", "svg");
            svgWrapper.setAttribute("xmlns", "http://www.w3.org/2000/svg");
            svgWrapper.setAttribute("width", div.offsetWidth);
            svgWrapper.setAttribute("height", div.offsetHeight);

            // Crear foreignObject para incluir HTML
            const foreignObject = document.createElementNS("http://www.w3.org/2000/svg",
                "foreignObject");
            foreignObject.setAttribute("width", "100%");
            foreignObject.setAttribute("height", "100%");

            // Clonar solo el contenido exportable
            const clone = div.cloneNode(true);
            foreignObject.appendChild(clone);
            svgWrapper.appendChild(foreignObject);

            // Serializar a string
            const serializer = new XMLSerializer();
            let source = serializer.serializeToString(svgWrapper);

            // Nombre seguro para el archivo
            let filename = div.querySelector('.barcode')?.dataset.name || 'producto_' + index;
            filename = filename.replace(/[^a-z0-9_\-]/gi, '_');

            zip.file(filename + '.svg', source);
        });

        // Generar y descargar ZIP
        const content = await zip.generateAsync({
            type: "blob"
        });
        const link = document.createElement("a");
        link.href = URL.createObjectURL(content);
        link.download = "codigos_barras.zip";
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        URL.revokeObjectURL(link.href);
    });
    </script>

</body>

</html>