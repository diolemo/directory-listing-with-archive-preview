<?php 

if (($c_dir = Context::$conf['content_dir']) !== null)
{
   $local = Request::$url->local;
   if (!$local) $local = 'index';
   $path = str_replace('/', '_', $local);
   $file = sprintf('%s/%s.html', $c_dir, $path);  
   
   if (is_file($file))
   {
      // needed for feedback to work
      Context::$session = Session::start();
      return require($file);
   }      
}

header('HTTP/1.0 404 Not Found');
if (Context::$conf['error_doc_404'] !== null)
   die(require(Context::$conf['error_doc_404']));

Content::$__auto_render = false;

?>
<!doctype html>
<html>
   <head>
      <title>File Not Found</title>
   </head>
   <body>
      <h1>File Not Found</h1>
   </body>
</html>