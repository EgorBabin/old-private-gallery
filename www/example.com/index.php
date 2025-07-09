<!DOCTYPE html>
  <html lang="ru">
  <head>
    <!---->
    <noscript>
        <meta http-equiv="refresh" content="0;url=non.php">
        <script>
            window.stop();
        </script>
    </noscript>

    <?php
      require 'S/checkauth.php';
    ?>
    <!---->
    <?php
      require 'defines.php';
    ?>
    <title><?php echo SITE_NAME . " — " . SITE_SLOGAN . " 📸"; ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex,nofollow">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="lib/swiper/swiper-bundle.min.css">
    <meta charset="UTF-8">
    <link rel="preload" href="S/video.js" as="script">
    <link rel="preload" href="S/display.js" as="script">
    <link rel="preload" href="S/touch.js" as="script">
    <style>
        .swiper {
            position: absolute;
            top: 0;
            left: 0;
            height: 110%;
            width: 100%;
            z-index: -1;
            object-fit: cover;
            touch-action: none;
        }
        .swiper-slide img {
            transform-origin: center center;
            will-change: transform;
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 6000ms linear !important;
        }
    </style>
  </head>
  <body>
    <button onclick="navigator.vibrate(35); window.scrollTo({ top: 0, behavior: 'smooth' })" class="gohome" title="Вверх">
        <img src="ico/up.svg" class="icotext">
    </button>
    <?php
      // Проверяем наличие сессии
      if ($_SESSION['auth']) {
        include 'S/progress-bar.html';

        require 'S/redirectURL.php';

        // Читаем json
        $jsonData = file_get_contents('cards.json');
        $cards = json_decode($jsonData, true);
        $amt = 0;
        if ($cards) {
          foreach ($cards as $card) {
            if (isset($card['amt']) && $card['amt'] !== "") {
              $amt += $card['amt'];
            }
            if (isset($card['video']) && $card['video'] !== "") {
              $video += $card['video'];
            }
          }
        }
        // Выводим количество фотографий в видео в целом
        echo "<p class='info'>Фотографий: " . $amt . ", видео: " . $video . "</p>";

        echo '<a style="display: none;" class="btn" href="#authors" onclick="navigator.vibrate(50);"><img class="icotext" src="ico/people.svg" /></a>';
        echo '<div class="container">';

        // Проверка на наличие данных
        if ($cards) {
            // Выводим каждую обложку по циклу
            foreach ($cards as $card) {
              $endValue = !empty($card['end']) ? ' - ' . $card['end'] : '';
              $video = !empty($card['video']) ? ' + ' . $card['video'] . ' &#127909;' : '';
              echo '
              <a onclick="navigator.vibrate(35)" href="' . $card['year'] . '/' . $card['category'] . '">
                <div class="card">
                    <div class="swiper">
                        <div class="swiper-wrapper">
                            <div class="swiper-slide"><img class="imgc" src="img/imgcard/' . $card['image'] . '" /></div>
                            <div class="swiper-slide"><img class="imgc" src="img/imgcard/1' . $card['image'] . '" /></div>
                            <div class="swiper-slide"><img class="imgc" src="img/imgcard/2' . $card['image'] . '" /></div>
                        </div>
                    </div>
                    <h4>' . $card['title'] . ' <img class="icotext" src="ico/' . $card['icon'] . '" /></h4>
                    <h5>' . $card['year'] . $endValue . ' год — ' . $card['amt'] . $video . '</h5>
                </div>
              </a>';
            }
        } else {
            echo '<p>Нет данных для отображения.</p>';
        }
          echo '<br>';
          echo '</div>';
          echo <<<EOT
          <script>
            function scrollToId(id) {
              var element = document.getElementById(id);
              if (element) {
                element.scrollIntoView();
              }
            }
          </script>
        EOT;
      } else {
          header('Location: non.php');
          exit;
        }
    ?>
    <script src="S/animi.js"></script>

    <script src="lib/swiper/swiper-bundle.min.js"></script>
    <script>
      // Настройка swiper
      document.addEventListener('DOMContentLoaded', function() {
          const swiper = new Swiper('.swiper', {
              loop: true,
              spaceBetween: 15,
              lazy: true,
              effect: "fade",
              speed: 500,
              autoplay: {
                  delay: 6000,
                  disableOnInteraction: false,
              },
              // Полное отключение свайпа
              noSwiping: true,
              noSwipingClass: 'swiper-slide',
              allowTouchMove: false,
              simulateTouch: false,
              touchStartPreventDefault: true,
              
              // Оптимизированные анимации
              on: {
                  init: function() {
                      // Только для десктопов - PC
                      if (!isMobile()) {
                          this.slides.forEach(slide => {
                              const img = slide.querySelector('img');
                              img.style.transform = 'scale(1)';
                              img.style.transition = 'transform 6000ms linear';
                          });
                      }
                  },
                  slideChangeTransitionStart: function() {
                      if (!isMobile()) {
                          this.slides.forEach(slide => {
                              slide.querySelector('img').style.transform = 'scale(1)';
                          });
                      }
                  },
                  autoplayTimeLeft: function(swiper, timeLeft, progress) {
                      if (!isMobile()) {
                          const currentImg = this.slides[this.activeIndex].querySelector('img');
                          const scaleValue = 1 + (0.1 * (1 - progress));
                          currentImg.style.transform = `scale(${scaleValue})`;
                      }
                  }
              }
          });

          // Проверка, мобильное ли устройство (чтобы отключить тяжёлые анимации)
          function isMobile() {
              return /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
          }
      });
    </script>
  </body>
</html>