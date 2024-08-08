<?php

namespace App\Services;

use App\Models\Addon;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\URL;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use ZipArchive;

class AddonInstallerService
{
    public function install(Request $request)
    {
        $zipFilePath = base_path('modules/addon.zip');
        
        try {
            $this->downloadAddon($request->input('purchase_code'), $request->input('addon'), $zipFilePath);
            $this->extractAddon($zipFilePath);

            // Check if the file exists before unlinking
            if (file_exists($zipFilePath)) {
                unlink($zipFilePath);
            }

            $addon = Addon::where('uuid', $request->input('uuid'))->update([
                'status' => 1
            ]);

            if($addon){
                $this->setupMetadata($request->input('uuid'));
            }

            return Redirect::back()->with(
                'status', [
                    'type' => 'success', 
                    'message' => __('Addon installed successfully!')
                ]
            );
        } catch (RequestException $e) {
            return $this->handleRequestException($e, $zipFilePath);
        } catch (\Exception $e) {
            return $this->handleGeneralException($e, $zipFilePath);
        }
    }

    protected function downloadAddon($purchaseCode, $addonName, $zipFilePath)
    {
        $client = new Client();
        $url = 'https://axis96.xyz/api/install/addon';

        $response = $client->post($url, [
            'form_params' => [
                'purchase_code' => $purchaseCode,
                'addon' => $addonName,
            ],
            'sink' => $zipFilePath,
        ]);

        if ($response->getStatusCode() != 200) {
            //unlink($zipFilePath);
            throw new \Exception('Failed to download the addon.');
        }
    }

    protected function extractAddon($zipFilePath)
    {
        $zip = new ZipArchive;

        if ($zip->open($zipFilePath) !== TRUE) {
            //unlink($zipFilePath);
            throw new \Exception('Failed to extract addon.');
        }

        $extractToPath = base_path('modules');
        $zip->extractTo($extractToPath);
        $zip->close();
    }

    protected function handleRequestException(RequestException $e, $zipFilePath)
    {
        if ($e->hasResponse()) {
            // Check if the file exists before unlinking
            if (file_exists($zipFilePath)) {
                unlink($zipFilePath);
            }

            $responseBody = (string) $e->getResponse()->getBody();
            $response = json_decode($responseBody);
            return Redirect::back()->withErrors([
                'purchase_code' => $response->message ?? 'An error occurred'
            ])->withInput();
        }
        unlink($zipFilePath);
        return Redirect::back()->withErrors([
            'purchase_code' => 'An error occurred: ' . $e->getMessage()
        ])->withInput();
    }

    protected function handleGeneralException(\Exception $e, $zipFilePath)
    {
        // Check if the file exists before unlinking
        if (file_exists($zipFilePath)) {
            unlink($zipFilePath);
        }
        
        return Redirect::back()->withErrors([
            'purchase_code' => 'An error occurred: ' . $e->getMessage()
        ])->withInput();
    }

    protected function setupMetadata($uuid)
    {
        $metadata = [
            "input_fields" => [
                [
                    "element" => "input",
                    "type" => "text",
                    "name" => "whatsapp_app_id",
                    "label" => "App ID",
                    "class" => "col-span-2"
                ],
                [
                    "element" => "input",
                    "type" => "text",
                    "name" => "whatsapp_client_id",
                    "label" => "Client id",
                    "class" => "col-span-1"
                ],
                [
                    "element" => "input",
                    "type" => "text",
                    "name" => "whatsapp_config_id",
                    "label" => "Config ID",
                    "class" => "col-span-1"
                ],
                [
                    "element" => "input",
                    "type" => "password",
                    "name" => "whatsapp_access_token",
                    "label" => "Access token",
                    "class" => "col-span-2"
                ],
                [
                    "element" => "input",
                    "type" => "password",
                    "name" => "whatsapp_client_secret",
                    "label" => "Client secret",
                    "class" => "col-span-2"
                ],
                [
                    "element" => "toggle",
                    "type" => "checkbox",
                    "name" => "is_embedded_signup_active",
                    "label" => "Enable/disable embedded signup",
                    "class" => "col-span-2"
                ]
            ]
        ];

        // Assuming YourModel is the model you want to update
        Addon::where('uuid', $uuid)->update(['metadata' => json_encode($metadata)]);
    }
}
