<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <title><cx:tag name="title">Page Title</cx:tag></title>
    <link rel="stylesheet" type="text/css" href="@styles/960.css" />
</head>
<body>
  <div class="container_12">
    <div class="grid_8 alpha">
      <h1><cx:tag name="title">Page Title</cx:tag></h1>
      <cx:region name="main"><p>Main Content Here!</p></cx:region>
    </div>
    <div class="grid_4 omega">
      <p>&nbsp;</p>
      <cx:region name="secondary" type="global"><p>Navigation and secondary content will be over here</p></cx:region>
    </div>
  </div>
  
  <div class="clear"></div>
  
  <div class="container_12">
    <div class="grid_12">
      <cx:region name="footer" type="global"><p>All contents copyright their respective authors. All rights reserved.</p></cx:region>
    </div>
  </div>
</body>
</html>