jQuery(document).ready(function ($) {
  /**
   * Countdown Timer
   */
  function initCountdown() {
    const countdownElement = $("#lp-countdown");
    if (!countdownElement.length) return;

    const eventDate = new Date(countdownElement.data("date")).getTime();
    if (isNaN(eventDate)) return;

    const timer = setInterval(function () {
      const now = new Date().getTime();
      const distance = eventDate - now;

      if (distance < 0) {
        clearInterval(timer);
        countdownElement.html("The event has started!");
        return;
      }

      const days = Math.floor(distance / (1000 * 60 * 60 * 24));
      const hours = Math.floor(
        (distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60)
      );
      const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
      const seconds = Math.floor((distance % (1000 * 60)) / 1000);

      $("#days").text(String(days).padStart(2, "0"));
      $("#hours").text(String(hours).padStart(2, "0"));
      $("#minutes").text(String(minutes).padStart(2, "0"));
      $("#seconds").text(String(seconds).padStart(2, "0"));
    }, 1000);
  }

  /**
   * Scroll Animations
   */
  function initScrollAnimations() {
    const observer = new IntersectionObserver(
      (entries, observer) => {
        entries.forEach((entry) => {
          if (entry.isIntersecting) {
            $(entry.target)
              .find(".lp-animate")
              .each(function () {
                const animationClass = $(this)
                  .closest("[data-animation]")
                  .data("animation");
                if (animationClass) {
                  $(this).addClass(animationClass);
                }
                $(this).addClass("in-view");
              });
            observer.unobserve(entry.target);
          }
        });
      },
      { threshold: 0.1 }
    );

    $(".lp-section").each(function () {
      observer.observe(this);
    });
  }

  /**
   * Music Player & Opening Modal
   */
  function initMusicAndModal() {
    const music = $("#lp-music")[0];
    const toggleBtn = $("#lp-music-toggle");
    const openBtn = $("#lp-open-invitation");
    const openingModal = $("#lp-opening-modal");
    const body = $("body");

    if (!openingModal.length) return;

    body.addClass("modal-open");

    const urlParams = new URLSearchParams(window.location.search);
    const guestName = urlParams.get("name");
    $(".guest-name").text(
      guestName
        ? decodeURIComponent(guestName.replace(/\+/g, " "))
        : "Tamu Undangan"
    );

    openBtn.on("click", function () {
      if (music) {
        music.play();
        toggleBtn.removeClass("paused").addClass("playing");
      }
      openingModal.addClass("closed");
      body.removeClass("modal-open");
      $("html, body").scrollTop(0);
    });

    if (music) {
      toggleBtn.on("click", function () {
        if (music.paused) {
          music.play();
          toggleBtn.removeClass("paused").addClass("playing");
        } else {
          music.pause();
          toggleBtn.removeClass("playing").addClass("paused");
        }
      });
    }
  }

  /**
   * RSVP Form Submission (AJAX)
   */
  function initRsvpForm() {
    $("#lp-rsvp-form").on("submit", function (e) {
      e.preventDefault();
      const form = $(this);
      const messageDiv = $("#lp-rsvp-message");
      const submitButton = form.find("button");
      const originalButtonText = submitButton.text();

      submitButton.text("Mengirim...").prop("disabled", true);
      messageDiv.text("").removeClass("success error");

      $.post(lovepress_ajax.ajax_url, {
        action: "lovepress_submit_rsvp",
        nonce: lovepress_ajax.nonce,
        post_id: form.find('input[name="post_id"]').val(),
        name: form.find('input[name="name"]').val(),
        attend: form.find('select[name="attend"]').val(),
      })
        .done(function (response) {
          if (response.success) {
            messageDiv.text(response.data.msg).css("color", "green");
            form.trigger("reset");
          } else {
            messageDiv.text(response.data.msg).css("color", "red");
          }
        })
        .fail(function () {
          messageDiv.text("Terjadi kesalahan. Coba lagi.").css("color", "red");
        })
        .always(function () {
          submitButton.text(originalButtonText).prop("disabled", false);
        });
    });
  }

  /**
   * Gift Section - Copy to Clipboard
   */
  function initGiftSection() {
    $(".lp-copy-button").on("click", function (e) {
      e.preventDefault();
      const button = $(this);
      const targetSelector = button.data("clipboard-target");
      const accountNumber = $(targetSelector).text().trim();
      const originalHTML = button.html();

      navigator.clipboard
        .writeText(accountNumber)
        .then(function () {
          button.html("Berhasil Disalin!").addClass("copied");
          setTimeout(function () {
            button.html(originalHTML).removeClass("copied");
          }, 2000);
        })
        .catch((err) => {
          console.error("Gagal menyalin:", err);
        });
    });
  }

  // Initialize all functions
  initCountdown();
  initScrollAnimations();
  initMusicAndModal();
  initRsvpForm();
  initGiftSection();
});
