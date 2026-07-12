<?php
/** Safe driver document upload handler. */
declare(strict_types=1);
function storeDriverDocument(array $file,int $driverId,string $type): array
{
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE)!==UPLOAD_ERR_OK || ($file['size'] ?? 0)>5*1024*1024) return [false,'Select a file smaller than 5 MB.'];
    $extension=strtolower(pathinfo((string)$file['name'],PATHINFO_EXTENSION)); if (!in_array($extension,['pdf','jpg','jpeg','png'],true)) return [false,'Only PDF, JPG, JPEG, and PNG files are allowed.'];
    $mime=(new finfo(FILEINFO_MIME_TYPE))->file((string)$file['tmp_name']); if (!in_array($mime,['application/pdf','image/jpeg','image/png'],true)) return [false,'The uploaded file type is invalid.'];
    $directory=UPLOAD_PATH.'/drivers'; ensureUploadDirectory($directory); $name=bin2hex(random_bytes(16)).'.'.$extension; if (!move_uploaded_file((string)$file['tmp_name'],$directory.'/'.$name)) return [false,'Could not store uploaded document.'];
    getDb()->prepare('INSERT INTO driver_documents (driver_id,file_name,file_path,document_type,uploaded_by) VALUES (?,?,?,?,?)')->execute([$driverId,basename((string)$file['name']),'uploads/drivers/'.$name,$type,currentUser()['id'] ?? null]);return [true,'Document uploaded.'];
}
