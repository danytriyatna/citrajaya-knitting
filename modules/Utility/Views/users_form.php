<?= $this->extend('template'); ?>
<?= $this->section('content'); ?>

<div class="container-fluid">
    <div class="row page-titles">
      <div class="col-md-5 align-self-center">
        <h4 class="text-themecolor"><?= $titlehead ?></h4>
      </div>
      <div class="col-md-7 align-self-center text-right">
        <div class="d-flex justify-content-end align-items-center">
          <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="./">Utilitas</a></li>
            <li class="breadcrumb-item active"><?= $titlehead ?></li>
          </ol>
        </div>
      </div>
    </div>

    <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="card-body">
            <div class="row">
              <div class="col-md-12 mb-3">
                <div class="row form-group">
                  <div class="col-sm-2">
                  <i class="fa fa-info-circle"></i> <i><b>(*)</b> wajib diisi !</i>
                  <div class="clearfix"></div>
                  </label>
                  </div>
                </div>

                <?php echo form_open_multipart(current_url(), array('class' => 'form-horizontal needs-validation', 'novalidate' => 'true')); ?>
                <?php
                $input = array(
                    'class' => 'form-control'
                );

                $label = array(
                    'class' => 'control-label text-start text-md-end col-sm-2'
                );
                ?>

                <div class="x_content">
                    <?php if (isset($_SESSION['message'])) { ?>
                        <script type="text/javascript">
                            window.setTimeout(function () {
                                $(".alert").alert('close');
                            }, 3000);
                        </script>
                        <div class="alert alert-info alert-dismissable">
                            <strong><b>Info! </b><br></strong><?php echo $_SESSION['message']; ?>
                        </div>
                    <?php } ?>
                    <?php if (isset($_SESSION['err'])) { ?>
                        <script type="text/javascript">
                            window.setTimeout(function () {
                                $(".alert").alert('close');
                            }, 5000);
                        </script>
                        <div class="alert alert-danger alert-dismissable">
                            <strong>Warning! </strong><?php echo $_SESSION['err']; ?>
                        </div>
                    <?php } ?>
                    
                    <div class="row">
                        <div class="col-md-12">

                            <div class="row form-group">
                                <?php echo form_label('Username*', 'username', $label) ?>
                                <div class="col-sm-8">
                                    <?php
                                    echo form_input($username);
                                    ?>
                                    <div class="invalid-feedback">
                                        Username tidak valid
                                    </div>
                                </div>
                            </div>

                            <div class="row form-group">
                                <?php echo form_label('Email*', 'email', $label) ?>
                                <div class="col-sm-8">
                                    <?php
                                    echo form_input($email);
                                    ?>
                                    <div class="invalid-feedback">
                                        Email tidak valid
                                    </div>
                                </div>
                            </div>

                            <div class="row form-group">
                                <?php echo form_label('NIP*', 'nip', $label) ?>
                                <div class="col-sm-8">
                                    <?php
                                    echo form_input($nip);
                                    ?>
                                    <div class="invalid-feedback">
                                        NIP tidak valid
                                    </div>
                                </div>
                            </div>

                            <div class="row form-group">
                                <?php echo form_label('Nama Lengkap*', 'full_name', $label) ?>
                                <div class="col-sm-8">
                                    <?php
                                    echo form_input($full_name);
                                    ?>
                                    <div class="invalid-feedback">
                                        Nama Lengkap tidak valid
                                    </div>
                                </div>
                            </div>

                            <div class="row form-group">
                                <?php echo form_label('Password*', 'password', $label) ?>
                                <div class="col-md-8">
                                    <?php
                                    echo form_password($password);
                                    ?>
                                    <small class="form-text">Min. 8 karakter, terdiri dari kombinasi huruf kecil, huruf kapital, simbol, dan angka</small>
                                    <div class="invalid-feedback">
                                        Password tidak valid
                                    </div>
                                </div>
                            </div>

                            <div class="row form-group">
                                <?php echo form_label('Konfirmasi Password*', 'repassword', $label) ?>
                                <div class="col-md-8">
                                    <?php
                                    echo form_password($password_confirm);
                                    ?>
                                    <div class="invalid-feedback">
                                        Konfirmasi Password tidak valid
                                    </div>
                                </div>
                            </div>

                            <div class="row form-group">
                                <?php echo form_label('Perusahaan*', 'idperusahaan', $label) ?>
                                <div class="col-md-8">
                                    <?php
                                    $attr = 'id="id_perusahaan" class="form-control select2" data-placeholder="-- Pilih Perusahaan --" required ';
                                    $selected_perusahaan = '';
                                    if (isset($user->id_perusahaan) && trim($user->id_perusahaan) != '') $selected_perusahaan = $user->id_perusahaan;
                                    echo form_dropdown('id_perusahaan', $perusahaan, $selected_perusahaan, $attr);
                                    ?>
                                    <div class="invalid-feedback">
                                        Harap pilih Perusahaan
                                    </div>
                                    <?php if ($selected_perusahaan == '') : ?>
                                    <script>
                                        document.getElementById("id_perusahaan").value = null;
                                    </script>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <div class="row form-group">
                                <?php echo form_label('User Role*', 'userrole', $label) ?>
                                <div class="col-md-8">
                                    <?php
                                    $attr = 'id="role_id" class="form-control select2" data-placeholder="-- Pilih User Role --" required ';
                                    $selected_role = '';
                                    if (isset($user->role_id) && trim($user->role_id) != '') $selected_role = $user->role_id;
                                    echo form_dropdown('role_id', $list_role, $selected_role, $attr);
                                    ?>
                                    <div class="invalid-feedback">
                                        Harap pilih User Role
                                    </div>
                                    <?php if ($selected_role == '') : ?>
                                    <script>
                                        document.getElementById("role_id").value = null;
                                    </script>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <div class="row form-group">
                              <?php echo form_label('Photo', 'file_id_photo', $label) ?>
                              <div class="col-sm-8">
                                  <?php 
                                  echo form_upload($file_id_photo);
                                  ?>
                                  <small class="form-text">Tipe file *.webp, *.jpg atau *.png, ukuran maks. 500 KB</small>
                              </div>
                            </div>

                            <div class="row form-group">
                                <?php echo form_label('','',$label) ?>
                                <div class="col-sm-8">
                                    <?php 
                                    if ($view_photo != ""){;
                                    ?>
                                    <img width="130px" class="img-thumbnail" src="uploads/users/<?= $view_photo; ?>">
                                    <?php }?>
                                </div>
                            </div>
                            
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-10 offset-sm-2">
                            <a href="utilitas/users" class="btn btn-default btn-sm m-e-5" type="submit">
                                <span class="fa fa-close"></span> Batal
                            </a>
                            <button id="submit" type="submit" class='btn btn-primary btn-sm'>
                                <span class="fa fa-save"></span> Simpan
                            </button>
                        </div>
                    </div>
                </div>
                <?php echo form_hidden('file_id_photo_old', $file_id_photo_old); ?>
                <?php echo form_hidden('id', $id); ?>
                <?php echo form_hidden($csrf); ?>
                <?php echo form_close(); ?>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

</div>

<?= $this->endSection('content'); ?>
<?= $this->section('script'); ?>
    <script>
        $(document).ready(function() {                            
            $('#role_id').select2();                     
            //$('#prefix').select2();                     
        });
    </script>
<?= $this->endSection('script'); ?>