<?php
include "includes/config.php";
if (session_status() === PHP_SESSION_NONE) session_start();
if (empty($_SESSION['csrf_token'])) $_SESSION['csrf_token'] = bin2hex(random_bytes(32));

$fullname=$email=$phone=$brand=$model=$year=$plate_number=$color=$appointment_date=$appointment_time=$remarks='';
$error=''; $selected_ids=[];

$categories=[]; $services=[];
$q=mysqli_query($conn,"SELECT id,category_name FROM service_categories WHERE status='Active' ORDER BY sort_order,category_name");
if($q) while($r=mysqli_fetch_assoc($q)) $categories[]=$r;
$q=mysqli_query($conn,"SELECT s.id,s.category_id,s.service_name,s.description,s.price,s.estimated_duration FROM service_catalog s JOIN service_categories c ON c.id=s.category_id WHERE s.status='Active' AND c.status='Active' ORDER BY c.sort_order,s.sort_order,s.service_name");
if($q) while($r=mysqli_fetch_assoc($q)) $services[]=$r;

if($_SERVER['REQUEST_METHOD']==='POST'){
  if(empty($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'],$_POST['csrf_token'])) $error='Invalid form request. Please refresh and try again.';
  $fullname=trim($_POST['fullname']??''); $email=strtolower(trim($_POST['email']??'')); $phone=trim($_POST['phone']??'');
  $brand=trim($_POST['brand']??''); $model=trim($_POST['model']??''); $year=trim($_POST['year']??''); $plate_number=strtoupper(trim($_POST['plate_number']??'')); $color=trim($_POST['color']??'');
  $appointment_date=$_POST['appointment_date']??''; $appointment_time=$_POST['appointment_time']??''; $remarks=trim($_POST['remarks']??'');
  $decoded=json_decode($_POST['selected_services']??'[]',true); if(is_array($decoded)) foreach($decoded as $id){$id=(int)$id;if($id>0)$selected_ids[$id]=$id;}
  if($error==='' && (!$fullname||!filter_var($email,FILTER_VALIDATE_EMAIL)||!$phone)) $error='Please complete your valid contact information.';
  elseif($error==='' && (!$brand||!$model||!$year||!$plate_number)) $error='Please complete your vehicle information.';
  elseif($error==='' && empty($selected_ids)) $error='Please add at least one service to your appointment.';
  elseif($error==='' && (!$appointment_date||!$appointment_time||strtotime("$appointment_date $appointment_time")<=time())) $error='Please select a future appointment schedule.';

  $selected_services=[];$total=0;
  if($error==='' && $selected_ids){
    $ids=implode(',',array_map('intval',array_values($selected_ids)));
    $res=mysqli_query($conn,"SELECT id,service_name,price,estimated_duration FROM service_catalog WHERE status='Active' AND id IN ($ids)");
    while($s=mysqli_fetch_assoc($res)){ $selected_services[]=$s; $total+=(float)$s['price']; }
    if(count($selected_services)!==count($selected_ids)) $error='One or more selected services are no longer available.';
  }
  if($error===''){
    $allowed=['08:00','08:30','09:00','09:30','10:00','10:30','11:00','11:30','12:00','12:30','13:00','13:30','14:00','14:30','15:00','15:30','16:00','16:30','17:00'];
    if(!in_array($appointment_time,$allowed,true)) $error='Please select a valid appointment time.';
  }
  if($error===''){
    mysqli_begin_transaction($conn);
    try{
      $st=mysqli_prepare($conn,"SELECT id FROM customers WHERE email=? LIMIT 1");mysqli_stmt_bind_param($st,'s',$email);mysqli_stmt_execute($st);$customer=mysqli_fetch_assoc(mysqli_stmt_get_result($st));mysqli_stmt_close($st);
      if($customer){$customer_id=(int)$customer['id'];$st=mysqli_prepare($conn,"UPDATE customers SET fullname=?,phone=? WHERE id=?");mysqli_stmt_bind_param($st,'ssi',$fullname,$phone,$customer_id);mysqli_stmt_execute($st);mysqli_stmt_close($st);}else{$st=mysqli_prepare($conn,"INSERT INTO customers(fullname,email,phone) VALUES(?,?,?)");mysqli_stmt_bind_param($st,'sss',$fullname,$email,$phone);mysqli_stmt_execute($st);$customer_id=mysqli_insert_id($conn);mysqli_stmt_close($st);}
      $st=mysqli_prepare($conn,"SELECT id FROM vehicles WHERE customer_id=? AND plate_number=? LIMIT 1");mysqli_stmt_bind_param($st,'is',$customer_id,$plate_number);mysqli_stmt_execute($st);$vehicle=mysqli_fetch_assoc(mysqli_stmt_get_result($st));mysqli_stmt_close($st);
      if($vehicle){$vehicle_id=(int)$vehicle['id'];$st=mysqli_prepare($conn,"UPDATE vehicles SET brand=?,model=?,year=?,color=? WHERE id=?");mysqli_stmt_bind_param($st,'ssisi',$brand,$model,$year,$color,$vehicle_id);mysqli_stmt_execute($st);mysqli_stmt_close($st);}else{$st=mysqli_prepare($conn,"INSERT INTO vehicles(user_id,customer_id,brand,model,year,plate_number,color) VALUES(NULL,?,?,?,?,?,?)");mysqli_stmt_bind_param($st,'ississ',$customer_id,$brand,$model,$year,$plate_number,$color);mysqli_stmt_execute($st);$vehicle_id=mysqli_insert_id($conn);mysqli_stmt_close($st);}
      $reference='VSMS-'.date('Ymd').'-'.strtoupper(bin2hex(random_bytes(3))); $service_type=implode(', ',array_column($selected_services,'service_name')); if(strlen($service_type)>100)$service_type=substr($service_type,0,97).'...';
      $st=mysqli_prepare($conn,"INSERT INTO reservations(reference_number,user_id,customer_id,vehicle_id,service_type,appointment_date,appointment_time,remarks,status,mechanic_id) VALUES(?,NULL,?,?,?,?,?,?,'Pending',NULL)");mysqli_stmt_bind_param($st,'siissss',$reference,$customer_id,$vehicle_id,$service_type,$appointment_date,$appointment_time,$remarks);mysqli_stmt_execute($st);$reservation_id=mysqli_insert_id($conn);mysqli_stmt_close($st);
      $st=mysqli_prepare($conn,"INSERT INTO reservation_services(reservation_id,service_id,service_name_snapshot,price_snapshot,duration_snapshot) VALUES(?,?,?,?,?)");foreach($selected_services as $s){$sid=(int)$s['id'];$price=(float)$s['price'];$dur=(int)$s['estimated_duration'];$name=$s['service_name'];mysqli_stmt_bind_param($st,'iisdi',$reservation_id,$sid,$name,$price,$dur);mysqli_stmt_execute($st);}mysqli_stmt_close($st);
      $method=$_POST['payment_method']??'Cash'; if(!in_array($method,['Cash','GCash','Bank Transfer','Card'],true))$method='Cash';
      $st=mysqli_prepare($conn,"INSERT INTO payments(reservation_id,amount,payment_method,status) VALUES(?,?,?,'Pending')");mysqli_stmt_bind_param($st,'ids',$reservation_id,$total,$method);mysqli_stmt_execute($st);mysqli_stmt_close($st);
      mysqli_commit($conn); header('Location: '.BASE_URL.'/appointment_success.php?ref='.urlencode($reference)); exit;
    }catch(Throwable $e){mysqli_rollback($conn);$error='We were unable to create your appointment. Make sure service_selector_integration.sql has been imported, then try again.';}
  }
}
include "includes/public_header.php";
?>
<link rel="stylesheet" href="<?= BASE_URL ?>/css/appointment-selector.css">
<section class="appointment-page">
    <div class="container py-5">
        <div class="appointment-heading"><span class="section-label">BOOK ONLINE</span>
            <h1>Make an Appointment</h1>
            <p>Select one or more services, enter your details, then review the appointment and payment before
                submitting.</p>
        </div>
        <?php if($error):?><div class="alert alert-danger"><?=htmlspecialchars($error)?></div><?php endif;?>
        <form method="post" id="appointmentForm" class="appointment-form"><input type="hidden" name="csrf_token"
                value="<?=htmlspecialchars($_SESSION['csrf_token'])?>"><input type="hidden" name="selected_services"
                id="selectedServicesInput"
                value='<?=htmlspecialchars(json_encode(array_values($selected_ids)),ENT_QUOTES)?>'>
            <div class="appointment-section">
                <div class="appointment-section-number">01</div>
                <div class="appointment-section-heading">
                    <h2>Choose Services</h2>
                    <p>Add everything your vehicle needs to the appointment cart.</p>
                </div>
                <div class="selector-topbar"><input id="serviceSearch" class="form-control" type="search"
                        placeholder="Search a service..."></div>
                <div class="category-tabs mb-3"><?php foreach($categories as $i=>$c):?><button type="button"
                        class="category-button <?=$i===0?'active':''?>"
                        data-category="<?=$c['id']?>"><?=htmlspecialchars($c['category_name'])?></button><?php endforeach;?>
                </div>
                <div class="service-grid" id="serviceList"><?php foreach($services as $s):?><article
                        class="service-card" data-category="<?=$s['category_id']?>"
                        data-name="<?=htmlspecialchars(strtolower($s['service_name']))?>">
                        <div>
                            <h3><?=htmlspecialchars($s['service_name'])?></h3>
                            <p><?=htmlspecialchars($s['description'])?></p><small><?=intval($s['estimated_duration'])?>
                                mins</small>
                        </div>
                        <div class="service-actions"><strong>₱<?=number_format($s['price'],2)?></strong><button
                                type="button" class="add-service-button" data-add-service data-id="<?=$s['id']?>"
                                data-name="<?=htmlspecialchars($s['service_name'])?>" data-price="<?=$s['price']?>">Add
                                to Cart</button></div>
                    </article><?php endforeach;?></div>
                <div class="cart-box">
                    <div><strong>Appointment Cart <span id="selectedBadge">0</span></strong>
                        <div id="selectedServices"><span class="text-muted">No services selected yet.</span></div>
                    </div>
                    <div class="cart-total">Total: <strong id="summaryTotal">₱0.00</strong></div>
                </div>
            </div>
            <div class="appointment-section">
                <div class="appointment-section-number">02</div>
                <div class="appointment-section-heading">
                    <h2>Your Information</h2>
                </div>
                <div class="row g-3">
                    <div class="col-md-4"><label>Full Name *</label><input class="form-control" name="fullname"
                            value="<?=htmlspecialchars($fullname)?>" required></div>
                    <div class="col-md-4"><label>Email *</label><input class="form-control" type="email" name="email"
                            value="<?=htmlspecialchars($email)?>" required></div>
                    <div class="col-md-4"><label>Phone *</label><input class="form-control" name="phone"
                            value="<?=htmlspecialchars($phone)?>" required></div>
                </div>
            </div>
            <div class="appointment-section">
                <div class="appointment-section-number">03</div>
                <div class="appointment-section-heading">
                    <h2>Vehicle Information</h2>
                </div>
                <div class="row g-3">
                    <div class="col-md-3"><label>Brand *</label><input class="form-control" name="brand"
                            value="<?=htmlspecialchars($brand)?>" required></div>
                    <div class="col-md-3"><label>Model *</label><input class="form-control" name="model"
                            value="<?=htmlspecialchars($model)?>" required></div>
                    <div class="col-md-2"><label>Year *</label><input class="form-control" type="number" name="year"
                            min="1900" max="<?=date('Y')+1?>" value="<?=htmlspecialchars($year)?>" required></div>
                    <div class="col-md-2"><label>Plate *</label><input class="form-control" name="plate_number"
                            value="<?=htmlspecialchars($plate_number)?>" required></div>
                    <div class="col-md-2"><label>Color</label><input class="form-control" name="color"
                            value="<?=htmlspecialchars($color)?>"></div>
                </div>
            </div>
            <div class="appointment-section">
                <div class="appointment-section-number">04</div>
                <div class="appointment-section-heading">
                    <h2>Appointment Schedule</h2>
                </div>
                <div class="row g-3">
                    <div class="col-md-4"><label>Date *</label><input class="form-control" type="date"
                            name="appointment_date" min="<?=date('Y-m-d')?>"
                            value="<?=htmlspecialchars($appointment_date)?>" required></div>
                    <div class="col-md-4"><label>Time *</label><select class="form-select" name="appointment_time"
                            required>
                            <option value="">Select Time</option>
                            <?php foreach(['08:00','08:30','09:00','09:30','10:00','10:30','11:00','11:30','12:00','12:30','13:00','13:30','14:00','14:30','15:00','15:30','16:00','16:30','17:00'] as $t):?>
                            <option value="<?=$t?>" <?=$appointment_time===$t?'selected':''?>>
                                <?=date('g:i A',strtotime($t))?></option><?php endforeach;?>
                        </select></div>
                    <div class="col-md-4"><label>Payment Method *</label><select class="form-select"
                            name="payment_method" id="paymentMethod">
                            <option>Cash</option>
                            <option>GCash</option>
                            <option>Bank Transfer</option>
                            <option>Card</option>
                        </select></div>
                    <div class="col-12"><label>Remarks</label><textarea class="form-control" name="remarks"
                            rows="3"><?=htmlspecialchars($remarks)?></textarea></div>
                </div>
            </div>
            <div class="appointment-submit">
                <div><strong>Ready to book?</strong>
                    <p>You will review customer, vehicle, schedule, services and payment before final submission.</p>
                </div><button type="button" class="appointment-submit-button" id="reviewButton">REVIEW APPOINTMENT
                    →</button>
            </div>
            <div class="review-overlay" id="reviewOverlay" hidden>
                <div class="review-modal"><button type="button" class="review-close" id="reviewClose">×</button>
                    <h2>Review Appointment & Payment</h2>
                    <div id="reviewContent"></div>
                    <div class="review-actions"><button type="button" class="btn btn-outline-secondary"
                            id="reviewBack">Edit</button><button type="submit" class="appointment-submit-button">CONFIRM
                            & SUBMIT</button></div>
                </div>
            </div>
        </form>
    </div>
</section>
<script src="<?= BASE_URL ?>/js/appointment-selector.js"></script>
<?php include "includes/public_footer.php"; ?>