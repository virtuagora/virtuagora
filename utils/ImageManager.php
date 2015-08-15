<?php

class ImageManager {

    public static function crearImagen($carpeta, $id, $nombre, $tamanos) {
        if (\Slim\Slim::getInstance()->getMode() != 'testing') {
            $dir = __DIR__ . '/../public/img/' . $carpeta . '/' . $id;
            if (!is_dir($dir)) {
                mkdir($dir, 0777, true);
            }
            $hash = md5(strtolower(trim($nombre)));
            foreach ($tamanos as $res) {
                $ch = curl_init('http://www.gravatar.com/avatar/'.$hash.'?d=identicon&f=y&s='.$res);
                $fp = fopen($dir . '/' . $res . '.png', 'wb');
                curl_setopt($ch, CURLOPT_FILE, $fp);
                curl_setopt($ch, CURLOPT_HEADER, 0);
                curl_exec($ch);
                curl_close($ch);
                fclose($fp);
            }
        }
    }

    public static function cambiarImagen($carpeta, $id, $tamanos) {
        if (\Slim\Slim::getInstance()->getMode() != 'testing') {
            $dir = __DIR__ . '/../public/img/' . $carpeta .'/' . $id;
            if (!is_dir($dir)) {
                mkdir($dir, 0777, true);
            }
            $storage = new \Upload\Storage\FileSystem($dir, true);
            $file = new \Upload\File('imagen', $storage);
            $filename = 'original';
            $file->setName($filename);
            $file->addValidations(array(
                new \Upload\Validation\Mimetype(array('image/png', 'image/jpg', 'image/jpeg', 'image/gif')),
                new \Upload\Validation\Size('1M')
            ));
            $file->upload();
            foreach ($tamanos as $res) {
                $image = new ZebraImage();
                $image->source_path = $dir . '/' . $file->getNameWithExtension();
                $image->target_path = $dir . '/' . $res . '.png';
                $image->preserve_aspect_ratio = true;
                $image->enlarge_smaller_images = true;
                $image->preserve_time = true;
                $image->resize($res, $res, ZEBRA_IMAGE_CROP_CENTER);
            }
        }
    }

}
