<form method="POST" action="{{ route('upload') }}"  enctype="multipart/form-data">

   <input type="file" name="file_upload" id="file-upload" />

   <input type="submit" name="submit" value="upload" />

</form>