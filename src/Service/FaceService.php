<?php

namespace App\Service;
use Symfony\Component\HttpFoundation\Request;
use Tzsk\Collage\MakeCollage;

class FaceService{
    private $url = 'https://westeurope.api.cognitive.microsoft.com/face/v1.0/detect?returnFaceId=true&returnFaceLandmarks=false&returnFaceAttributes=age,gender&recognitionModel=recognition_01&returnRecognitionModel=false';
    private $key1 = '0edff8387fac4d68b294d7a052aee12c';
    private $key2 = 'e15112589b084ea6a1750d447a4249ac';

    public function __construct(){

    }

    public function downloadImg($uniqId,$url){
        try{
            file_put_contents($uniqId.'.jpg', fopen($url, 'r'));
            return true;
        }catch (\Exception $e){
            echo $e;
            return false;
        }
    }

    public function delImg($file){
        try{
            unlink($file) or die("Couldn't delete file");
            return true;
        }catch (\Exception $e){
            echo $e;
            try{
                $myFile = fopen($file, 'w') or die("can't open file");
                fclose($myFile);
                unlink($file) or die("Couldn't delete file");
                return true;
            }catch (\Exception $e){
                echo $e;
                return false;
            }
        }
    }

    /**
     * @param $file - Should be the full path of file
     * @return false || JSON
     */
    public function apiCall($file){
        $ch = curl_init();
        $url = 'https://westeurope.api.cognitive.microsoft.com/face/v1.0/detect?returnFaceId=true&returnFaceLandmarks=false&returnFaceAttributes=age,gender&recognitionModel=recognition_01&returnRecognitionModel=false';
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POSTFIELDS, file_get_contents($file));
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-type: application/octet-stream','Ocp-Apim-Subscription-Key: 0edff8387fac4d68b294d7a052aee12c')); // Assuming you're requesting JSON
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        try{
            $response = curl_exec($ch);
        }catch (\Exception $e){
            echo $e;
            return false;
        }
        $data = json_decode($response);
        return $data;
    }

    public function checkGender($data){
        if (sizeof($data) === 0){
            echo 'No face found';
            return -1;
        }
        $male = 0;
        $female = 0;
        for($i = 0 ; $i < sizeof($data) ; $i++){
            $data[$i]->faceAttributes->gender === "male" ? $male++ : $female++;
        }
        return $male > $female;
    }

    public function checkAge($data){
        if (sizeof($data) === 0){
            echo 'No face found';
            return -1;
        }
        $age = 0;
        $ages = sizeof($data);
        for($i = 0 ; $i < sizeof($data) ; $i++){
            $age = $age + $data[$i]->faceAttributes->age;
        }
        return $age/$ages;
    }

    public function collage($imgs){
        $collage = new MakeCollage('gd');
        $image = MakeCollage::make(400, 400)->from($imgs);

    }

    public function oldcollage($imgs){
        $collageSpec = array();
        $collageSpec['height'] = 1800;
        $collageSpec['width'] = 1800;
        $collageSpec['images'] = array();
        $x = 0;
        $y = 0;
        for($i = 0; $i<9 ; $i++){
            //$i % 2 || $i === 0 || $i % 1 ? $x = $x + 300 : $y = $y + 300;
            array_push($collageSpec['images'],
                array(
                    'url' => $imgs[$i],
                    'x' => $x,
                    'y' => $y,
                    'width' => 599,
                    'height' => 599,
                    'rotate' => 0,
                )
            );
            // after the array push because of the first occurence which should be 0,0
            if($i % 3 === 0){
                $y = $y + 600;
                $x = 0;
            }
            else{
                $x = $x + 600;
            }
        }
        $ch = curl_init('http://collageapi.congolabs.com/create_collage.json');
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($collageSpec));
        header('Content-Type: image/jpeg');
        $response = curl_exec($ch);
        echo $response;
        curl_close($ch);
    }
}