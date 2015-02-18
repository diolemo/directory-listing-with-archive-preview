<?php

Context::$conf = array();

// the base url of the website
// but limited to the path only
Context::$conf['base_url'] = '/';

// the framework will try to load files from this
// directory when no other file is found (404)
// * null value will disable this
Context::$conf['content_dir'] = 'html';

// the character encoding to send
// in the content-type header
Context::$conf['encoding'] = 'utf-8';

// the html document to use for 404 errors
Context::$conf['error_doc_404'] = 'html/error_404.html';

// the default content-type for header
Context::$conf['mime_type'] = 'text/html';

// the file to show when user is not auth'd
Context::$conf['no_auth_file'] = 'html/error_auth.html';

// cookie and timeout params for able sessions
Context::$conf['session_cookie'] = 'able_session';
Context::$conf['session_timeout'] = 7200;

// directory to load *.sql files from
Context::$conf['sqloader_dir'] = 'sql';

// template file for auto rendering of documents
Context::$conf['template'] = 'html/template.php';

// the environment level for error reporting
Context::$conf['environment'] = Context::ENV_PRODUCTION;

?>