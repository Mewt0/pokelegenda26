<?

function plus_user_pok($text)
{
$filename = 'admin/plus_user_pok.dat';
$somecontent = "$text\r\n";
// Вначале давайте убедимся, что файл существует и доступен для записи.
if (is_writable($filename)) {
// В нашем примере мы открываем $filename в режиме "дописать в конец".
    // Таким образом, смещение установлено в конец файла и
    // наш $somecontent допишется в конец при использовании fwrite().
    if (!$handle = fopen($filename, 'a')) {
    exit;
    }
// Записываем $somecontent в наш открытый файл.
    if (fwrite($handle, $somecontent) === FALSE) {
        exit;
    }
    fclose($handle); } else {}}


function upes_user_pok($text)
{
$filename = 'admin/upes_user_pok.dat';
$somecontent = "$text\r\n";
// Вначале давайте убедимся, что файл существует и доступен для записи.
if (is_writable($filename)) {
// В нашем примере мы открываем $filename в режиме "дописать в конец".
    // Таким образом, смещение установлено в конец файла и
    // наш $somecontent допишется в конец при использовании fwrite().
    if (!$handle = fopen($filename, 'a')) {
    exit;
    }
// Записываем $somecontent в наш открытый файл.
    if (fwrite($handle, $somecontent) === FALSE) {
        exit;
    }
    fclose($handle); } else {}
}
    
function minus_user_pok($text)
{
$filename = 'admin/minus_user_pok.dat';
$somecontent = "$text\r\n";
// Вначале давайте убедимся, что файл существует и доступен для записи.
if (is_writable($filename)) {
// В нашем примере мы открываем $filename в режиме "дописать в конец".
    // Таким образом, смещение установлено в конец файла и
    // наш $somecontent допишется в конец при использовании fwrite().
    if (!$handle = fopen($filename, 'a')) {
    exit;
    }
// Записываем $somecontent в наш открытый файл.
    if (fwrite($handle, $somecontent) === FALSE) {
        exit;
    }
    fclose($handle); } else {}}
?>