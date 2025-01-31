<?php
  include('./php/include/dbconn.php');

    // 세션이 이미 시작되었는지 확인
    if (session_status() === PHP_SESSION_NONE) {
      session_start();
    }
    if (isset($_SESSION['id'])) {
      $id = $_SESSION['id'];
      $name = $_SESSION['name'];
    }else{
      echo "<script>
              alert('로그인이 필요한 서비스입니다.');
              window.location.href = './login.php';
            </script>";
    }

    // question count
    $query = "select count(*) from room WHERE id='$id'";
    $result = mysqli_query($conn, $query);
    $max_Num = mysqli_fetch_array($result);

    // 전체 목록의 갯수
    $num = $max_Num[0];
    //한 페이지에 보여질 게시물 개수
    $list_num = 5;    
    //이전, 다음 버튼 클릭시 나오는 페이지 수
    $page_num =3;    
    //현재 페이지
    $page = isset($_GET["page"])? $_GET["page"] : 1;    
    // 전체페이지수 계산
    $total_page = ceil($num / $list_num); 
    //전체블럭 계산
    $total_block = ceil($total_page / $page_num);    
    //현재블럭번호 계산
    $now_block = ceil($page / $page_num);    
    //블럭당 시작페이지 번호$start
    $s_pageNum = ($now_block - 1) * $page_num + 1;    
    //데이터가 0인 경우
    if($s_pageNum <= 0){ $s_pageNum = 1; };    
    //블럭당 마지막페이지 번호
    $e_pageNum = $now_block * $page_num;    
    //마지막 번호가 전체 페이지번호보다 크다면 동일한 값을 준다.
    if($e_pageNum > $total_page){ $e_pageNum = $total_page; };

    $start = ($page - 1) * $list_num;
    $cnt = $start + 1;
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>나의 예약</title>
  <!-- 공통 헤드정보 삽입 -->
  <?php include('./php/include/head.php'); ?>
  <style>
    section{
      padding: 0 20px;
    }
    /* 768px 이상일때 요소배치 */
    @media (min-width: 768px) {
      section{
        width: 600px;
        margin: 50px auto 0 auto;
      }
    }
    .reserve_list > h2{
      font-size: var(--fs-large);
      font-weight: var(--fw-bold);
      padding: 40px 0 10px 0;
      text-align: center;
    }
    .table>:not(caption)>*>*{
      padding: 12px;
      vertical-align: middle;
    }
    .reserve_list .table{
      border-top: 2px solid #000;
    }
    .reserve_list .table thead{
      text-align: center;
    }
    .reserve_list .table tbody{
      border-top: 1px solid #ccc;
      border-bottom: 1px solid #ccc;
      text-align: center;
    }

    /* 상태버튼 */
    .status_btn{
      background:var(--red);
      color:#fff;
      padding:5px;
      text-wrap:nowrap;
      border-radius:5px;
    }
    .status_btn_disabled {
      background-color: #ccc; 
      color: #666;
      cursor: not-allowed;
      pointer-events: none;
    }
    .status_btn_disabled:hover {
      background-color: #ccc;
      text-decoration: none;
    }

  </style>
</head>
<body>
  <!-- 공통헤더삽입 -->
  <?php include('./php/include/header.php');?>

  <!-- 나의 문의 내역 -->
  <main>
    <section class="reserve_list">
      <p class="bread_c">홈 &#62; 마이페이지 &#62; <b>나의 예약</b></p>
      <h2>나의 예약</h2>
      <!-- 테이블 시작 -->
      <article class="reserve_detail">
        <table class="table table-hover table-responsive reserve_table mt-3 mb-3">
          <caption style="display:none;">나의 예약 리스트</caption>
          <thead>
            <tr>
              <th>번호</th>
              <th>날짜</th>
              <th>시간</th>
              <th>장소</th>
              <th>상태</th>
            </tr>
          </thead>
          <tbody>
          <!-- 전체강의 보기 -->
          <?php 
            // 현재 시간
            $currentDateTime = new DateTime();

            // room 불러오는곳
            $sql = "SELECT * FROM room WHERE id='$id' ORDER BY no DESC LIMIT $start, $list_num;";
            $result = mysqli_query($conn, $sql);

            while($q = mysqli_fetch_row($result)) {
              // 예약 날짜와 시간을 형식화
              $date = date("Y.m.d", strtotime($q[3]));
              $startTime = date("H:i", strtotime($q[4]));
              $endTime = date("H:i", strtotime($q[5]));
              $roomNumber = htmlspecialchars($q[2]);

              // 시간 비교
              $reserveDateTime = new DateTime($q[3] . ' ' . $q[4]); // 예약 날짜와 시작 시간 결합
              $isExpired = $reserveDateTime < $currentDateTime;

              // 버튼 텍스트와 스타일 결정
              $buttonText = $isExpired ? '종료' : '취소';
              $buttonClass = $isExpired ? 'status_btn status_btn_disabled' : 'status_btn';

              print 
              "<tr>
                <td>$max_Num[0]</td>
                <td>$date</td>
                <td>$startTime - $endTime</td>
                <td style='text-wrap:nowrap;'>$roomNumber<span>호</span></td>
                <td>
                  <a href='./php/reserve_delete.php?no=".$q[0]."' title='예약취소버튼' class='$buttonClass'>$buttonText</a>
                </td>
              </tr>";

              $max_Num[0]--;
            }
          ?>

        </table>

        <!-- 페이지 네이션 -->
        <nav aria-label="페이지네이션" class="padding50 mt-3 mb-3" style="position: relative;">
          <ul class="pagination justify-content-center">

          <?php //페이지네이션이 들어가는 곳
            //이전페이지
            if($page <= 1){ ?> 
              <li class="page-item"><a href="notice_list.php?class_no='<?echo $class_no;?>'page=<?php echo ($page-1); ?>" class="page-link"><i class="bi bi-chevron-left"></i></a></li>
              <?php } 
              else{ ?> 
              <li class="page-item"><a href="notice_list.php?class_no='<?echo $class_no;?>'page<?php echo ($page-1); ?>" class="page-link "><i class="bi bi-chevron-left"></i></a></li>
              <?php };
              ?> 
          <?php //여기서부터 페이지 번호출력하기
            for($print_page=$s_pageNum;$print_page<=$e_pageNum;$print_page++){?>
              <li class="page-item"><a href="notice_list.php?class_no='<?echo $class_no;?>'page<?php echo $print_page; ?>" class="page-link">
                <?php echo $print_page ?>
              </a></li>
            <?php }; ?>  

            <!-- 다음 버튼 나오는 곳 -->
            <?php if($page>=$total_page){ ?>
              <li class="page-item"><a href="notice_list.php?class_no='<?echo $class_no;?>'page<?php echo $total_page; ?>" title="다음페이지로" class="page-link"><i class="bi bi-chevron-right"></i></i></a></li>
            <?php }else{ ?>
              <li class="page-item"><a href="notice_list.php?class_no='<?echo $class_no;?>'page<?php echo ($page+1); ?>" class="page-link ">
              <i class="bi bi-chevron-right"></i></a></li>
          <?php };    
          ?>    
          </ul>
        </nav>  

        <!-- 바로가기 가기 -->
        <div class="btn-box-l mb-5">
          <a href="reserve_check.php" class="btn-l">예약하기</a>
          <a href="mypage.php" class="btn-l">마이페이지로</a>
        </div>
      </article>
    </section>
  </main>

  <!-- 공통푸터삽입 -->
  <?php include('./php/include/footer.php');?>
  <!-- 공통바텀바삽입 -->
  <?php include('./php/include/bottom.php');?>

</body>
</html>