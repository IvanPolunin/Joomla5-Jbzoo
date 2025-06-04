<?php
/**
* @package   BaForms
* @author    Balbooa http://www.balbooa.com/
* @copyright Copyright @ Balbooa
* @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
*/

defined('_JEXEC') or die;

require_once JPATH_ROOT.'/components/com_baforms/libraries/google-2.0/vendor/autoload.php';

class drive
{
    private $client = null;

    public function __construct($client_id, $client_secret)
    {
        $this->client = new Google_Client();
        $scope = [
            'https://www.googleapis.com/auth/userinfo.email',
            'https://www.googleapis.com/auth/drive'
        ];
        $this->client->setApplicationName('Balbooa Google Drive');
        $this->client->addScope($scope);
        $this->client->setClientId($client_id);
        $this->client->setClientSecret($client_secret);
        $this->client->setRedirectUri('urn:ietf:wg:oauth:2.0:oob');
        $this->client->setAccessType('offline');
    }

    public function getAuthentication()
    {
        $authUrl = $this->client->createAuthUrl();

        return $authUrl;
    }

    public function createAccessToken($code)
    {
        try {
            $token = $this->client->authenticate($code);
            $accessToken = json_encode($token);
        } catch (Exception $e) {
            $accessToken = 'INVALID_TOKEN';
        }

        return $accessToken;
    }

    protected function setAccessToken($accessToken)
    {
        $this->client->setAccessToken($accessToken);
        if ($this->client->isAccessTokenExpired()) {
            $token = json_decode($accessToken);
            $this->client->refreshToken($token->refresh_token);
        }
    }

    public function getFolders($accessToken)
    {
        $this->setAccessToken($accessToken);
        $pageToken = null;
        $folders = [];
        $service = new Google_Service_Drive($this->client);
        $oauth = new Google_Service_Oauth2($this->client);
        $userinfo = $oauth->userinfo->get();
        $type = 'application/vnd.google-apps.folder';
        $email = $userinfo->email;
        $q = "mimeType='{$type}' and trashed = false and '{$email}' in writers";
        do {
            $params = [
                'pageToken' => $pageToken,
                'spaces' => 'drive',
                'q' => $q
            ];
            $results = $service->files->listFiles($params);
            $pageToken = $results->getNextPageToken();
            foreach ($results as $result) {
                if ($result->mimeType == $type) {
                    $folder = new stdClass();
                    $folder->id = $result->id;
                    $folder->title = $result->name;
                    $folders[] = $folder;
                }
            }
        } while ($pageToken != null);

        return $folders;
    }

    public function uploadFiles($accessToken, $files, $folder)
    {
        $this->setAccessToken($accessToken);
        $service = new Google_Service_Drive($this->client);
        foreach ($files as $file) {
            $metadata = [
                'name' => $file->name,
                'parents' => [
                    $folder
                ]
            ];
            $fileMetadata = new Google_Service_Drive_DriveFile($metadata);
            $content = file_get_contents($file->path);
            $settings = [
                'data' => $content,
                'uploadType' => 'resumable',
                'fields' => 'id'
            ];
            $file = $service->files->create($fileMetadata, $settings);
        }
    }
}