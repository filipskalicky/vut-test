<?php

helper('error_page');

echo view('errors/page', error_page_data(403));
