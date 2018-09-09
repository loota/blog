<?php
// mkdir target
// dd bs=1024 count=49 if=/dev/urandom of=readme.txt
// dd bs=1024 count=51 if=/dev/urandom of=big-readme.txt
// dd bs=1024 count=101 if=/dev/urandom of=big-image.png
// dd bs=1024 count=99 if=/dev/urandom of=image.png
// dd bs=1024 count=99 if=/dev/urandom of=raargh.txt
// touch image.jpeg
// touch image.gif
// touch -d"2 years ago" image.jpeg
// touch -d"2 years ago" readme.txt

// Go through files in a directory
// gif - remove
// jpeg - rename to jpg
// png - resize to half size, if size > 100KB
// txt - zip, if size > 50KB
//
// Over year old files (mtime) are removed, unless they are txt files with size < 50KB
//
// If the filename matches the shell glob *raargh*, copy the file with 
// additional extension .bkp
//
// User can type in an extension which will not be affected by the program.

//$omitted = fgets(STDIN);
//if ($omitted !== $pathinfo['extension'] . "\n") {
//}
//if ($omitted !== "gif\n") {
//}

date_default_timezone_set('Europe/Helsinki');

// The straight solution
$i = new DirectoryIterator("target");
foreach ($i as $filename) {
    $filepath = "target/" . $filename;
    if (is_file($filepath)) {
        $pathinfo = pathinfo($filepath);
        switch ($pathinfo['extension']) {
            case 'gif': 
                echo 'remove ' . $filepath . "\n"; 
                break;
            case 'jpeg': 
                echo 'rename ' . $filepath . "\n";
                break;
            case 'png':
                if (filesize($filepath) > 1024 * 100) {
                    echo 'resize ' . $filepath . "\n";
                }
                break;
            case 'txt':
                if (filesize($filepath) > 1024 * 50) {
                    echo 'zip ' . $filepath . "\n";
                }
                break;
        } 
        if (filemtime($filepath) < strtotime('1 year ago')) {
            if ($pathinfo['extension'] !== 'txt' || filesize($filepath) > 1024 * 50) { 
                echo 'remove ' . $filepath . "\n";
            }
        }
        if (fnmatch("*raargh*", $filepath)) {
            echo "copy " . $filepath . "\n";
        }
    }
}

echo "\n\n";

// The procedural, factored solution
function handleGif($filepath)
{
    echo 'remove ' . $filepath . "\n"; 
    handleAll($filepath);
}
function handleJpeg($filepath)
{
    echo 'rename ' . $filepath . "\n";
    handleAll($filepath);
}
function handlePng($filepath)
{
    if (filesize($filepath) > 1024 * 100) {
        echo 'resize ' . $filepath . "\n";
    }
    handleAll($filepath);
}
function handleTxt($filepath)
{
    if (filesize($filepath) > 1024 * 50) {
        echo 'zip ' . $filepath . "\n";
    }
    if (filesize($filepath) > 1024 * 50) { 
        handleOld($filepath);
    }
    handleName($filepath);
}
function handleOld($filepath)
{
    if (filemtime($filepath) < strtotime('1 year ago')) {
        echo 'remove ' . $filepath . "\n";
    }
}
function handleAll($filepath)
{
    handleOld($filepath);
    handleName($filepath);
}
function handleName($filepath)
{
    if (fnmatch("*raargh*", $filepath)) {
        echo "copy " . $filepath . "\n";
    }
}
$i = new DirectoryIterator("target");
foreach ($i as $filename) {
    $filepath = "target/" . $filename;
    if (is_file($filepath)) {
        $pathinfo = pathinfo($filepath);
        switch ($pathinfo['extension']) {
            case 'gif': 
                handleGif($filepath);
                break;
            case 'jpeg': 
                handleJpeg($filepath);
                break;
            case 'png': 
                handlePng($filepath);
                break;
            case 'txt':
                handleTxt($filepath);
                break;
        } 
    }
}

echo "\n\n";

// Object-Oriented solution
class OldMinimizer
{
    public function __construct($filepath)
    {
        $this->filepath = $filepath;
    }
    public function minimize()
    {
        if (filemtime($this->filepath) < strtotime('1 year ago')) {
            echo 'remove ' . $this->filepath . "\n";
        }
    }
}
class NameMatchingMinimizer extends OldMinimizer
{
    public function minimize()
    {
        if (fnmatch("*raargh*", $this->filepath)) {
            echo "copy " . $this->filepath . "\n";
        }
        parent::minimize();
    }
}
class GifMinimizer extends NameMatchingMinimizer
{
    public function minimize()
    {
        echo 'remove ' . $this->filepath . "\n"; 
        parent::minimize();
    }
}
class JpegMinimizer extends NameMatchingMinimizer
{
    public function minimize()
    {
        echo 'rename ' . $this->filepath . "\n";
        parent::minimize();
    }
}
class PngMinimizer extends NameMatchingMinimizer
{
    public function minimize()
    {
        if (filesize($this->filepath) > 1024 * 100) {
            echo 'resize ' . $this->filepath . "\n";
        }
        parent::minimize();
    }
}
class TxtMinimizer extends NameMatchingMinimizer
{
    public function minimize()
    {
        if (filesize($this->filepath) > 1024 * 50) {
            echo 'zip ' . $this->filepath . "\n";
        }
        if (filesize($this->filepath) > 1024 * 50) {
            parent::minimize();
        }
    }
}
$i = new DirectoryIterator("target");
foreach ($i as $filename) {
    $filepath = "target/" . $filename;
    if (is_file($filepath)) {
        $pathinfo = pathinfo($filepath);
        switch ($pathinfo['extension']) {
            case 'gif':
                $file = new GifMinimizer($filepath);
                break;
            case 'jpeg':
                $file = new JpegMinimizer($filepath);
                break;
            case 'png': 
                $file = new PngMinimizer($filepath);
                break;
            case 'txt':
                $file = new TxtMinimizer($filepath);
                break;
        } 
        $file->minimize();
    }
}
