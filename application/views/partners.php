
<form action="<?php echo base_url(); ?>import" method="post" enctype="multipart/form-data">
    <h4>Import Excel</h4>
    <input type="file" class="form-control" name="uploadFile" required>
    <button type="submit" name="submit" value="Upload" class="btn btn-info">Upload</button>
</form>

<br><hr><br>

<a href="<?php echo base_url(); ?>export/"><i class="fas fa-file-excel"></i> Export Excel</a>
                       
          






