<?php

namespace App\Services;

use ArPHP\I18N\Arabic;
use Barryvdh\DomPDF\Facade\Pdf;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\View\View;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Mccarlosen\LaravelMpdf\Facades\LaravelMpdf;

class GeneratePDFService
{

    public static function generate(View|\Illuminate\Contracts\View\View $view, $paperSize = 'A5', $paperOrientation = 'portrait')
    {


        $viewHtml = $view->render();
        /*
                $arabic = new Arabic();
                $p = $arabic->arIdentify($viewHtml);

                for ($i = count($p) - 1; $i >= 0; $i -= 2) {
                    $utf8ar = $arabic->utf8Glyphs(substr($viewHtml, $p[$i - 1], $p[$i] - $p[$i - 1]));
                    $viewHtml = substr_replace($viewHtml, $utf8ar, $p[$i - 1], $p[$i] - $p[$i - 1]);
                }*/
        $mPdf = LaravelMpdf::loadHTML($viewHtml, [
            'curlAllowUnsafeSslRequests' => true,
            'showImageErrors' => true,
            'format' => $paperSize . '-' . ucfirst($paperOrientation)[0],
        ]);
        $mPdf->getMpdf()->curlAllowUnsafeSslRequests = true;
        return $mPdf;
    }
}
