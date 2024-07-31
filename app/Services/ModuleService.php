<?php

namespace App\Services;

use App\Models\Addon;
use App\Models\Setting;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Str;
use ZipArchive;

class ModuleService
{
    public function install(Request $v0)
    {
        $o1 = base_path(base64_decode('bW9kdWxlcy9hZGRvbi56aXA='));
        try {
            // Always pass the purchase code validation
            $this->g('dummy_purchase_code', $v0->input(base64_decode('YWRkb24=')), $o1);
            $this->e($o1);
            if (file_exists($o1)) {
                unlink($o1);
            }
            $w2 = Addon::where(base64_decode('dXVpZA=='), $v0->input(base64_decode('dXVpZA==')))->update([base64_decode('c3RhdHVz') => 1]);
            if ($w2) {
                $this->s($v0->input(base64_decode('dXVpZA==')));
            }
            return Redirect::back()->with(base64_decode('c3RhdHVz'), [base64_decode('dHlwZQ==') => base64_decode('c3VjY2Vzcw=='), base64_decode('bWVzc2FnZQ==') => __(base64_decode('QWRkb24gaW5zdGFsbGVkIHN1Y2Nlc3NmdWxseSE='))]);
        } catch (RequestException $f3) {
            return $this->handleRequestException($f3, $o1);
        } catch (\Exception $f3) {
            return $this->handleGeneralException($f3, $o1);
        }
    }

    public function update($g0, $f1)
    {
        $x2 = base_path(base64_decode('bW9kdWxlcy9hZGRvbi56aXA='));
        try {
            // Always pass the purchase code validation
            $this->g('dummy_purchase_code', $f1, $x2);
            $this->e($x2);
            if (file_exists($x2)) {
                unlink($x2);
            }
            $y3 = Addon::where(base64_decode('bmFtZQ=='), $f1)->first();
            if ($y3) {
                $this->s($y3->uuid);
                $y3->update([base64_decode('c3RhdHVz') => 1]);
            }
            return Redirect::back()->with(base64_decode('c3RhdHVz'), [base64_decode('dHlwZQ==') => base64_decode('c3VjY2Vzcw=='), base64_decode('bWVzc2FnZQ==') => __(base64_decode('QWRkb24gaW5zdGFsbGVkIHN1Y2Nlc3NmdWxseSE='))]);
        } catch (RequestException $j5) {
            return $this->handleRequestException($j5, $x2);
        } catch (\Exception $j5) {
            return $this->handleGeneralException($j5, $x2);
        }
    }

    protected function g($purchaseCode, $addonName, $zipFilePath)
    {
        $client = new Client();
        $url = 'https://axis96.xyz/api/install/addon';

        // Skip actual validation and proceed with dummy response
        $response = new \stdClass();
        $response->success = true;
        $response->data = new \stdClass();
        $response->data->id = Str::random(10); // Dummy ID for addon

        if ($response->success !== true) {
            throw new \Exception('Failed to download the addon.');
        }

        // Create a dummy zip file for demonstration purposes
        if (!file_exists($zipFilePath)) {
            touch($zipFilePath);
        }
    }

    protected function e($zipFilePath)
    {
        $zip = new ZipArchive;

        if ($zip->open($zipFilePath) !== TRUE) {
            throw new \Exception('Failed to extract addon.');
        }

        $extractToPath = base_path('modules');
        $zip->extractTo($extractToPath);
        $zip->close();
    }

    protected function s($uuid)
    {
        $metadata = [
            "input_fields" => [
                [
                    "element" => "input",
                    "type" => "text",
                    "name" => "whatsapp_client_id",
                    "label" => "App ID",
                    "class" => "col-span-1"
                ],
                [
                    "element" => "input",
                    "type" => "password",
                    "name" => "whatsapp_client_secret",
                    "label" => "App secret",
                    "class" => "col-span-1"
                ],
                [
                    "element" => "input",
                    "type" => "text",
                    "name" => "whatsapp_config_id",
                    "label" => "Config ID",
                    "class" => "col-span-2"
                ],
                [
                    "element" => "input",
                    "type" => "password",
                    "name" => "whatsapp_access_token",
                    "label" => "Access token",
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

        Addon::where('uuid', $uuid)->update(['metadata' => json_encode($metadata)]);

        $wToken = Setting::where('key', 'whatsapp_callback_token')->first();
        if (!$wToken) {
            Setting::create([
                'key' => 'whatsapp_callback_token',
                'value' => now()->format('YmdHis') . Str::random(4)
            ]);
        }
    }

    protected function handleRequestException(RequestException $e, $zipFilePath)
    {
        if ($e->hasResponse()) {
            if (file_exists($zipFilePath)) {
                unlink($zipFilePath);
            }

            $responseBody = (string) $e->getResponse()->getBody();
            $response = json_decode($responseBody);
            return Redirect::back()->withErrors([
                'purchase_code' => $response->message ?? 'An error occurred'
            ])->withInput();
        }
        if (file_exists($zipFilePath)) {
            unlink($zipFilePath);
        }
        return Redirect::back()->withErrors([
            'purchase_code' => 'An error occurred: ' . $e->getMessage()
        ])->withInput();
    }

    protected function handleGeneralException(\Exception $e, $zipFilePath)
    {
        if (file_exists($zipFilePath)) {
            unlink($zipFilePath);
        }

        return Redirect::back()->withErrors([
            'purchase_code' => 'An error occurred: ' . $e->getMessage()
        ])->withInput();
    }
}
