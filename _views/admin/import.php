<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/dropzone/4.3.0/min/dropzone.min.css" integrity="sha512-zoIoZAaHj0iHEOwZZeQnGqpU8Ph4ki9ptyHZFPe+BmILwqAksvwm27hR9dYH4WXjYY/4/mz8YDBCgVqzc2+BJA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
<form 
  action="<?= admin_url( 'admin-ajax.php' ) ?>" 
  id="ufc-datatable-dropzone" 
  method="post" 
  class="dropzone wrapper">
  <h2>Drag and drop or select a JSON file</h2>
  <br />
  <input type="hidden" name="action" value="ufc_datatable_json_import" />
  <input type="hidden" name="_wpnonce" value="<?= wp_create_nonce('ufc_datatable_json_import') ?>" />
  <div class="file-upload">
    <svg class="upload-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512">
      <path d="M384 352v64c0 17.67-14.33 32-32 32H96c-17.67 0-32-14.33-32-32v-64c0-17.67-14.33-32-32-32s-32 14.33-32 32v64c0 53.02 42.98 96 96 96h256c53.02 0 96-42.98 96-96v-64c0-17.67-14.33-32-32-32S384 334.3 384 352zM201.4 9.375l-128 128c-12.51 12.51-12.49 32.76 0 45.25c12.5 12.5 32.75 12.5 45.25 0L192 109.3V320c0 17.69 14.31 32 32 32s32-14.31 32-32V109.3l73.38 73.38c12.5 12.5 32.75 12.5 45.25 0s12.5-32.75 0-45.25l-128-128C234.1-3.125 213.9-3.125 201.4 9.375z"/>
    </svg>
  </div>
  <div id="ufc-datatable-dropzone-preview-template" style="display: none;">
    <div class="dz-preview dz-file-preview">
    <div class="dz-image">
      <img data-dz-thumbnail="">
    </div>
    <div class="dz-details">
      <div class="dz-size">
      <span data-dz-size=""></span>
    </div>
    <div class="dz-filename">
      <span data-dz-name=""></span>
    </div>
  </div>
  <div class="dz-progress">
    <span class="dz-upload" data-dz-uploadprogress=""></span>
  </div>
  <div class="dz-error-message">
    <span data-dz-errormessage=""></span>
  </div>
</form>
<script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/dropzone/4.3.0/min/dropzone.min.js" integrity="sha512-/sbdXPs3O51Y1wPvSzJehJxpFzVVzVv+FoA0WxyVmYZXt9k4ruNLSH4ElXdUrWTIAN9FRhY3YZ7ITnVVqZwxXw==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script>
  Dropzone.autoDiscover = false;
  jQuery('#ufc-datatable-dropzone').dropzone({
    previewTemplate: document.querySelector('#ufc-datatable-dropzone-preview-template').innerHTML,
    maxFilesize: 256,
    paramName: "ufc_datatable_json_file",
    filesizeBase: 1000,
    maxFiles: 1,
    acceptedFiles: '.json',
    thumbnail: function(file, dataUrl) {
      if (file.previewElement) {
        file.previewElement.classList.remove("dz-file-preview");
        var images = file.previewElement.querySelectorAll("[data-dz-thumbnail]");
        for (var i = 0; i < images.length; i++) {
          var thumbnailElement = images[i];
          thumbnailElement.alt = file.name;
          thumbnailElement.src = dataUrl;
        }
        setTimeout(function() { file.previewElement.classList.add("dz-image-preview"); }, 1);
      }
    },
    success: function(file, response){
      if(!response.success) {
        // show message
        return Swal.fire({
          title: 'Error!',
          html: response.data.message,
          icon: 'error',
          confirmButtonText: 'Ok'
        });
      }

      // remove
      this.removeFile(file);

      // show message
      Swal.fire({
        title: 'Success!',
        text: response.data.message,
        icon: 'success',
        confirmButtonText: 'Ok'
      });
    },
    error: function(file, response){
      // remove
      this.removeFile(file);

      // show message
      Swal.fire({
        title: 'Error!',
        text: 'Error encountered during upload, please try again!',
        icon: 'error',
        confirmButtonText: 'Ok'
      });
    }
  });

  jQuery(document).on('click', '.file-upload', () => {
    jQuery('#ufc-datatable-dropzone').click();
  });
</script>
<style>
  .wrapper {
    min-height: 91vh;
    width: 97.5%;
    height: 100%;
    margin: 10px 0;
    display: flex;
    align-items: center;
    justify-content: center;
    border: 4px dashed #2590EB;
    flex-flow: column nowrap;
  }
  .wrapper .upload-icon {
    height: 100px;
    fill: #fff;
  }
  .wrapper .file-upload {
    height: 200px;
    width: 200px;
    border-radius: 100px;
    position: relative;
    display: flex;
    justify-content: center;
    align-items: center;
    border: 4px solid #FFFFFF;
    overflow: hidden;
    background-image: linear-gradient(to bottom, #2590EB 50%, #FFFFFF 50%);
    background-size: 100% 200%;
    transition: all 1s;
    color: #FFFFFF;
    font-size: 100px;
  }
  .wrapper .file-upload input[type=file] {
    height: 200px;
    width: 200px;
    position: absolute;
    top: 0;
    left: 0;
    opacity: 0;
    cursor: pointer;
  }

  .wrapper .file-upload:hover {
    background-position: 0 -100%;
    color: #2590EB;
  }
  .wrapper .file-upload:hover .upload-icon {
    fill: #2590EB;
  }
</style>
