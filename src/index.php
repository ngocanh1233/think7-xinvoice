
<!doctype html>
<html lang="en">
  <head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">

    <title>Think7 xInvoice</title>
    <style>
      .error{
        color: red;
        font-size: 12px;
      }
    </style>
    <script src="//code.jquery.com/jquery-1.11.3.min.js"></script>
    <script type="text/javascript" src="http://ajax.aspnetcdn.com/ajax/jquery.validate/1.13.1/jquery.validate.min.js"></script>
  </head>
  <body>
    <div class="vh-100 d-flex flex-column justify-content-start align-items-center">
        <ul class="nav nav-pills px-3 mb-3 mt-5 container-sm" id="pills-tab" role="tablist">
          <li class="nav-item w-50" role="presentation">
            <a href="#" class="nav-link text-center active" id="pills-home-tab" data-bs-toggle="pill" data-bs-target="#pills-home" role="tab" aria-controls="pills-home" aria-selected="true">Convert to x-Invoice</a>
          </li>
          <li class="nav-item text-center w-50" role="presentation">
            <a href="#" class="nav-link" id="pills-profile-tab" data-bs-toggle="pill" data-bs-target="#pills-profile" role="tab" aria-controls="pills-profile" aria-selected="false">Read x-Invoice</A>
          </li>
        </ul>

        <div class="tab-content container-sm" id="pills-tabContent">
          <div class="tab-pane fade show active" id="pills-home" role="tabpanel" aria-labelledby="pills-home-tab" tabindex="0">
              <div class="card p-4 container-sm">
                <h3>Convert to x-Invoice</h3>
                <form id="conver-form" action="/result.php" class="mt-4" method="POST" enctype="multipart/form-data">
                  <div class="row mb-3">
                    <div class="form-group col-md-6">
                      <label for="inputEmail4">PDF File</label>
                      <input required="required" accept="pdf" type="file" class="form-control" name="dm_File" id="dm_File">
                    </div>
                    <div class="form-group col-md-6">
                      <label for="inputPassword4">XML File</label>
                      <input required="required" accept="xml" type="file" class="form-control" name="dm_XMLFile" id="dm_XMLFile">
                    </div>
                  </div>
                  <div class="text-center"><button type="submit" class="btn btn-primary">Combine</button></div>
                </form>
              </div>
          </div>
          <div class="tab-pane fade" id="pills-profile" role="tabpanel" aria-labelledby="pills-profile-tab" tabindex="0">
              <div class="card p-4 container-sm">
                <h3>Read x-Invoice</h3>
                <form id="read-form" action="/resultRead.php" class="mt-4" method="POST" enctype="multipart/form-data">
                  <div class="row mb-3">
                    <div class="form-group col-md-6">
                      <label for="inputEmail4">X-Invoice File</label>
                      <input required="required" accept="pdf" type="file" class="form-control" name="dm_File" id="dm_File">
                    </div>
                  </div>
                  <div class="text-center"><button type="submit" class="btn btn-primary">Read Data</button></div>
                </form>
              </div>
          </div>
        </div>
    </div>
    <script>
      $(document).ready(function() {
        $("#conver-form").validate({
          rules: {
            dm_File: "required",
            dm_XMLFile: "required",
          },
          messages: {
            dm_File: "Please input PDF File",
            dm_XMLFile: "Please input XML File",
          }
        });
        $("#read-form").validate({
          rules: {
            dm_File: "required",
          },
          messages: {
            dm_File: "Please input xinvoice PDF File",
          }
        });
      });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js" integrity="sha384-I7E8VVD/ismYTF4hNIPjVp/Zjvgyol6VFvRkX/vR+Vc4jQkC+hVqc2pM8ODewa9r" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.min.js" integrity="sha384-BBtl+eGJRgqQAUMxJ7pMwbEyER4l1g+O15P+16Ep7Q9Q+zqX6gSbd85u4mG4QzX+" crossorigin="anonymous"></script>
  </body>
</html>