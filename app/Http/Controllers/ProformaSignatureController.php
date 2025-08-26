<?php
namespace App\Http\Controllers;

use App\Models\Proforma;
use App\Services\DocuSignService;
use Illuminate\Http\Request;
use App\Services\DocuSignAuthService;
use DocuSign\eSign\Client\ApiClient;

class ProformaSignatureController extends Controller
{
    protected $docuSignService;

    public function __construct(DocuSignService $docuSignService)
    {
        $this->docuSignService = $docuSignService;
    }

   public function sendForSignature(Proforma $proforma, DocuSignService $docuSignService)
    {
        $url = $docuSignService->sendProformaToSign($proforma);

        return redirect()->away($url); // ✅ redirige vers DocuSign pour signature intégrée
    }

}
