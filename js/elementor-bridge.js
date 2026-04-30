(function ($) {
  "use strict";

  var cfg = window.dk_speakout_elementor_bridge || {};
  if (!cfg.ajaxurl || !cfg.nonce) {
    return;
  }

  function getPostId($card) {
    var className = $card.attr("class") || "";
    var match = className.match(/\bpost-(\d+)\b/);
    return match ? parseInt(match[1], 10) : 0;
  }

  function collectMissingPostIds($scope) {
    var ids = [];
    $scope.find(".elementor-post").each(function () {
      var $card = $(this);
      if ($card.find(".dk-speakout-elementor-card-teaser").length) {
        $card.attr("data-dk-speakout-ready", "1");
        return;
      }
      var postId = getPostId($card);
      if (!postId) return;
      if ($card.attr("data-dk-speakout-loading") === "1" || $card.attr("data-dk-speakout-ready") === "1") {
        return;
      }
      $card.attr("data-dk-speakout-loading", "1");
      ids.push(postId);
    });
    return ids;
  }

  function renderCard($card, cardData) {
    var $target = $card.find(".elementor-post__read-more-wrapper").first();
    if (!$target.length) {
      $card.attr("data-dk-speakout-loading", "0");
      return;
    }
    var countHtml = cardData.count_html || "";
    var readMoreText = cfg.read_more || "Read more";
    var html = '<div class="dk-speakout-elementor-card-teaser">';
    if (countHtml) {
      html +=
        '<div class="dk-speakout-progress-wrap dk-speakout-progress-wrap-top dk-speakout-progress-loop">' +
        countHtml +
        "</div>";
    }
    html += '<a class="dk-speakout-readme dk-speakout-readmore-loop" href="' +
      cardData.read_more +
      '"><span>' +
      readMoreText +
      "</span></a>" +
      "</div>";

    $card.find(".dk-speakout-elementor-card-teaser").remove();
    $target.before(html);
    $card.attr("data-dk-speakout-loading", "0");
    $card.attr("data-dk-speakout-ready", "1");
  }

  function applyCards(responseCards) {
    $(".elementor-post").each(function () {
      var $card = $(this);
      var postId = getPostId($card);
      if (!postId) return;
      if (!responseCards[String(postId)]) {
        $card.attr("data-dk-speakout-loading", "0");
        return;
      }
      renderCard($card, responseCards[String(postId)]);
    });
  }

  function loadCards(scope) {
    var $scope = $(scope || document);
    var postIds = collectMissingPostIds($scope);
    if (!postIds.length) return;

    $.ajax({
      url: cfg.ajaxurl,
      method: "POST",
      dataType: "json",
      data: {
        action: "dk_speakout_elementor_cards",
        nonce: cfg.nonce,
        post_ids: postIds
      }
    }).done(function (res) {
      if (!res || !res.success || !res.data || !res.data.cards) {
        return;
      }
      applyCards(res.data.cards);
    }).always(function () {
      $(".elementor-post[data-dk-speakout-loading='1']").attr("data-dk-speakout-loading", "0");
    });
  }

  $(function () {
    loadCards(document);
    var observer = new MutationObserver(function () {
      loadCards(document);
    });
    observer.observe(document.body, { childList: true, subtree: true });
  });
})(jQuery);

