<?php

use App\Models\GlobalOption;
use App\Enums\GlobalOptionEnum;
use App\Models\PurchaseList;
use App\Models\SaleList;
use \Illuminate\Support\Facades\File;
use Intervention\Image\Facades\Image;


$headerImage = Image::make(public_path(GlobalOption::find(GlobalOptionEnum::HeaderImage)->getRawOriginal('value')));

$footerImage = Image::make(public_path(GlobalOption::find(GlobalOptionEnum::FooterImage)->getRawOriginal('value')));

// Get the height of the image
$paperSize = $paperSize ?? 'A5';
$paperOrientation = $paperOrientation ?? 'portrait';
$isPortrait = $paperOrientation === 'portrait';
$paperWidth = match ($paperSize) {
    'A5' => $isPortrait ? 148 : 210,
    'A4' => $isPortrait ? 210 : 297,
    default => $isPortrait ? 210 : 297,
};

$dpi = $dpi ?? 96;

$pageWidth = ($paperWidth / 25.4) * $dpi;
$pageRightLeftMargins = 25;
$pageBottomMargin = 25;
$pageTopMargin = 15;

$pageWidthWithMargins = $pageWidth - (2 * $pageRightLeftMargins);
$headerAspect = $headerImage->height() / $headerImage->width();

$headerHeight = min($pageWidthWithMargins * $headerAspect, $headerImage->height(), 350);


$footerAspect = $footerImage->height() / $footerImage->width();
$footerHeight = min($pageWidthWithMargins * $footerAspect, $footerImage->height(), 130);

?>
<?php

?>
<html lang="ar" dir="rtl">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<head>
    <title>
        @yield('title')
    </title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>

    <style>
        @font-face {
            font-family: 'tajawal';
            src: local('Tajawal'), local('Tajawl'), url("{{storage_path('fonts/Tajawal-Regular.ttf')}}") format('truetype');
        }

        body, span, div, td, tr, th {
            direction: rtl;
            font-family: 'tajawal', serif;
        }

        table {
            width: 100%;
            border: 2px solid black;
            border-collapse: collapse;
            /*page-break-after: always;*/
        }

        table thead th {
            background: #fde7a2;
        }

        th, td {
            border: 1px solid black;
            padding: 1mm 2mm;
        }

        td {
            text-align: right;
        }

    </style>
    <style>
        @page {
            margin: {{$headerHeight+$pageTopMargin}}px {{$pageRightLeftMargins}}px {{$footerHeight+$pageBottomMargin}}px {{$pageRightLeftMargins}}px;
            header: page-header;
            footer: page-footer;
        }

        header {
            position: fixed;
            top: -{{$headerHeight}}px;
            left: 0;
            right: 0;
        }

        footer {
            position: fixed;
            bottom: -{{$footerHeight}}px;
            left: 0;
            right: 0;
        }

        table.empty-table, table.empty-table td {
            border: 0;
        }
    </style>

</head>
<body style="font-size: 20px" dir="rtl">
<htmlpageheader name="page-header">
    <div style="text-align: center;width: 100%">
        <img style="width: 100%;max-width: 100%;max-height: {{$headerHeight}}px;"
             src="{{GlobalOption::get(GlobalOptionEnum::HeaderImage,true)}}" alt="">
    </div>
</htmlpageheader>
@yield('content')
<htmlpagefooter name="page-footer">
    <div style="text-align: center;width: 100%;">
        @if($asPdf)
            <div style="text-align: center;font-family: monospace; font-weight: bold; font-size: 0.9rem">
                <span>-</span>
                <span class="pagenum" style="padding: 0 2px">{PAGENO}</span>
                <span>-</span>
            </div>
        @endif
        <img style="width: 100% ;max-width: 100%; max-height: {{$footerHeight}}px;"
             src="{{GlobalOption::get(GlobalOptionEnum::FooterImage ,true)}}" alt="">
    </div>
</htmlpagefooter>

</body>
</html>
