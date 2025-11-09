
   <div class="row">
      <div class="col-lg-12">
         <div class="card">
            <div class="card-body">
               <div class="d-flex flex-wrap align-items-center justify-content-between">
                  <div class="d-flex flex-wrap align-items-center">
                     <div class="profile-img position-relative me-3 mb-3 mb-lg-0 profile-logo profile-logo1">
                        <img src="https://templates.iqonic.design/hope-ui/pro/html/assets/images/icons/01.png" alt="User-Profile" class="theme-color-default-img img-fluid rounded-pill avatar-100" loading="lazy">
                          </div>
                     <div class="d-flex flex-wrap align-items-center mb-3 mb-sm-0">
                        <h4 class="me-2 h4"><?= !empty($model['full_name']) ? $model['full_name'] : "Not Set" ?></h4>
                        <span> - Premium User </span>
                     </div>
                  </div>
               </div>
            </div>
         </div>
      </div>
      <div class="col-lg-8">
         <div class="card">
            <div class="card-header d-flex align-items-center justify-content-between">
               <div class="header-title">
                  <h4 class="card-title">Gallery</h4>
               </div>
               <span>132 pics</span>
            </div>
            <div class="card-body">
                <div class="d-grid gap-card grid-cols-3">
                    <a data-fslightbox="gallery" href="../../assets/images/icons/04.png">
                    <img src="https://templates.iqonic.design/hope-ui/pro/html/assets/images/icons/04.png" class="img-fluid bg-soft-info rounded" alt="profile-image" loading="lazy">
                    </a>
                    <a data-fslightbox="gallery" href="../../assets/images/shapes/02.png">
                    <img src="https://templates.iqonic.design/hope-ui/pro/html/assets/images/shapes/02.png" class="img-fluid bg-soft-primary rounded" alt="profile-image" loading="lazy">
                    </a>
                    <a data-fslightbox="gallery" href="../../assets/images/icons/08.png">
                    <img src="https://templates.iqonic.design/hope-ui/pro/html/assets/images/icons/08.png" class="img-fluid bg-soft-info rounded" alt="profile-image" loading="lazy">
                    </a>
                    <!-- <a data-fslightbox="gallery" href="../../assets/images/shapes/04.png">
                    <img src="https://templates.iqonic.design/hope-ui/pro/html/assets/images/shapes/04.png" alt="profile-image" loading="lazy">
                    </a> -->
                    <a data-fslightbox="gallery" href="../../assets/images/icons/02.png">
                    <img src="https://templates.iqonic.design/hope-ui/pro/html/assets/images/icons/02.png" class="img-fluid bg-soft-warning rounded" alt="profile-image" loading="lazy">
                    </a>
                    <a data-fslightbox="gallery" href="../../assets/images/shapes/06.png">
                    <img src="https://templates.iqonic.design/hope-ui/pro/html/assets/images/shapes/06.png" class="img-fluid bg-soft-primary rounded" alt="profile-image" loading="lazy">
                    </a>
                    <a data-fslightbox="gallery" href="../../assets/images/icons/05.png">
                    <img src="https://templates.iqonic.design/hope-ui/pro/html/assets/images/icons/05.png" class="img-fluid bg-soft-danger rounded" alt="profile-image" loading="lazy">
                    </a>
                    <a data-fslightbox="gallery" href="../../assets/images/shapes/08.png">
                    <img src="https://templates.iqonic.design/hope-ui/pro/html/assets/images/shapes/04.png" class="img-fluid bg-soft-primary rounded" alt="profile-image" loading="lazy">
                    </a>
                    <a data-fslightbox="gallery" href="../../assets/images/icons/01.png">
                    <img src="https://templates.iqonic.design/hope-ui/pro/html/assets/images/icons/01.png" class="img-fluid bg-soft-success rounded" alt="profile-image" loading="lazy">
                    </a>
                </div>
            </div>
         </div>
      </div>
      
      <div class="col-lg-4">
         <div class="card">
            <div class="card-header">
               <div class="header-title">
                  <h4 class="card-title">About</h4>
               </div>
            </div>
            <div class="card-body">
                    
               <div class="mb-1">Name: <a href="#" class="ms-3"><?= !empty($model['full_name']) ? $model['full_name'] : "Not Set" ?></a></div>
               <div class="mb-1">UserName: <a href="#" class="ms-3"><?= !empty($model['username']) ? $model['username'] : "Not Set" ?></a></div>
               <div class="mb-1">Email: <a href="#" class="ms-3"><?= !empty($model['email']) ? $model['email'] : "Not Set" ?></a></div>
               <div class="mb-1">Phone: <a href="#" class="ms-3"><?= !empty($model['contact_no']) ? $model['contact_no'] : "Not Set" ?></a></div>
               <div class="mb-1">DOB: <a href="#" class="ms-3"><?= !empty($model['date_of_birth']) ? $model['date_of_birth'] : "Not Set" ?></a></div>
               <div>Location: <span class="ms-3">USA</span></div>
            </div>
         </div>
      </div>
   </div>
