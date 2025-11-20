<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>No Events</title>
  
  <?php include "header.html";?>
  <style>
    :root{
      --bg: #ffffff;
      --card: #ffffff;
      --text: #0d1633;
      --muted: #5b6a96;
      --accent: #3b63ff;
      --ring: #3652ff22;
    }
    * { box-sizing: border-box; }
    html, body { height: 100%; }
    body{
      margin:0;
      display:grid;
      place-items:center;
      background: var(--bg);
      font-family: system-ui, -apple-system, Segoe UI, Roboto, "Helvetica Neue", Arial, "Noto Sans", "Apple Color Emoji", "Segoe UI Emoji", "Segoe UI Symbol", sans-serif;
      color: var(--text);
    }
    .card{
      width:min(560px, 92vw);
      background: var(--card);
      border: 1px solid #e0e6f5;
      border-radius: 20px;
      padding: 28px 26px;
      box-shadow: 0 18px 50px -20px rgba(19, 36, 84, .1),
                  0 0 0 8px var(--ring);
      text-align:center;
      animation: pop .5s ease-out;
    }
    @keyframes pop{
      from{ transform: translateY(6px) scale(.98); opacity:0 }
      to{ transform: translateY(0) scale(1); opacity:1 }
    }
    .icon{
      width: 70px; height:70px; margin: 6px auto 14px;
      display:grid; place-items:center;
      border-radius: 20px;
      background: #f3f6ff;
      border: 1px solid #dbe4ff;
      position:relative;
    }
    .icon::before{
      content:"";
      width: 34px; height: 34px;
      border: 3px solid var(--accent);
      border-radius: 10px;
      transform: rotate(45deg);
      display:block;
    }
    .icon::after{
      content:"";
      position:absolute;
      width: 38px; height: 2.5px;
      background: #ff6b6b;
      transform: rotate(-45deg);
      border-radius: 2px;
    }
    h1{
      margin: 8px 0 6px;
      font-size: clamp(18px, 2.6vw, 24px);
      letter-spacing: .2px;
    }
    p{
      margin: 0 0 18px;
      color: var(--muted);
      font-size: 14px;
      line-height: 1.5;
    }
    .btn{
      display:inline-block;
      padding: 10px 14px;
      border-radius: 12px;
      border: 1px solid #2b3a70;
      background: var(--accent);
      color: #fff;
      text-decoration:none;
      font-weight: 600;
      transition: transform .12s ease, box-shadow .12s ease, border-color .12s ease;
    }
    .btn:hover{
      transform: translateY(-1px);
      box-shadow: 0 10px 24px -10px rgba(91,140,255,.6);
      border-color: var(--accent);
    }
    .logout{
      display:inline-block;
      margin-top:15px;
      font-size:14px;
      color:#e74c3c;
      text-decoration:none;
      font-weight:600;
      transition: color .2s;
    }
    .logout:hover{
      color:#c0392b;
    }

    /* Recent Events Section */
    .recent-events{
      margin-top:30px;
      text-align:left;
      border-top:1px solid #e6ebf5;
      padding-top:20px;
    }
    .recent-events h2{
      font-size:17px;
      margin-bottom:14px;
      color: var(--text);
      font-weight:600;
    }
    .event-list{
      list-style:none;
      padding:0;
      margin:0;
    }
    .event-list li{
      padding:12px 14px;
      background:#f8faff;
      border:1px solid #e0e6f5;
      border-radius:12px;
      margin-bottom:10px;
      font-size:14px;
      display:flex;
      justify-content:space-between;
      align-items:center;
      transition: all .2s ease;
      cursor:pointer;
    }
    .event-list li span{
      color: var(--muted);
      font-size:13px;
    }
    .event-list li:hover{
      background:#eef2ff;
      transform: translateX(2px);
    }
  </style>
</head>
<body>
  <main class="card" role="status" aria-live="polite">
    <div class="icon" aria-hidden="true"></div>
    <h1>Sorry, there is no event</h1>
    <p>
      We couldn’t find any upcoming events right now. Please check back later.
    </p>
    <a class="btn" href="#" onclick="location.reload()">Refresh</a>
    <br>
    <a class="logout" href="logout.php">Log out</a>

    <!-- Recent Events Section -->
    <div class="recent-events">
      <h2>See Recent Events</h2>
      <ul class="event-list">
      <?php
        $sql = $con->query("SELECT * FROM tbl_event ORDER BY ev_id DESC LIMIT 5");
        while($row = mysqli_fetch_assoc($sql)){
          $start = date("F j, Y", strtotime($row['start']));
          $end   = date("F j, Y", strtotime($row['end']));
      ?>
        <li id='<?= $row['ev_id'] ?>'>
          <?= htmlspecialchars($row['ev_name']) ?>
          <span><?= $start ?> → <?= $end ?></span>
        </li>
      <?php }?>
    </ul>

    </div>
  </main>
<script>
  $(".event-list > li").click(function(){
    let id = $(this).attr("id");
    window.location.href = window.location.href + "?e_id="+id;
  })
</script>
</body>
</html>
