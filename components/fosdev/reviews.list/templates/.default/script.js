window.addEventListener('DOMContentLoaded', function() {
    /* Functions
    ************************************************************************************************************************************************************************************
    ************************************************************************************************************************************************************************************/
    function getModalPhotos(photoId) {
      var modalFosRev = document.getElementById('modalFosRevPhotos');
      if(modalFosRev !== null) {
        modalFosRev.style.display = 'block';
        document.body.classList.add("fos-reviews-body-hidden");
        var sliderItems = document.querySelectorAll('.fosrev-modal__slider__i');
        sliderItems.forEach(function(item) {
          item.classList.remove('fosrev-modal__slider__i_active');
        });
        var activeSliderItem = document.querySelector('.fosrev-modal__slider__i[data-counter="' + photoId + '"]');
        activeSliderItem.classList.add('fosrev-modal__slider__i_active');
        var modalElement = document.querySelector('.fosrev-modal');
        modalElement.scrollTop = 0;
      } else {
        var xhr = new XMLHttpRequest();
        xhr.open('GET', '/bitrix/tools/fosreviews/getPhotos.php?photoId='+photoId, true);
        xhr.onreadystatechange = function() {
          if (xhr.readyState === XMLHttpRequest.DONE && xhr.status === 200) {
            var template = xhr.responseText;

            var modalContainer = document.createElement('div');
            modalContainer.innerHTML = template;
            document.body.appendChild(modalContainer);

            document.body.classList.add("fos-reviews-body-hidden");

            // Add event handlers photos
            var thumbnails = document.querySelectorAll('.fosrev-modal__list__i');
            for (var i = 0; i < thumbnails.length; i++) {
              thumbnails[i].addEventListener('click', function() {
                var counter = this.getAttribute('data-counter');
                var sliderItems = document.querySelectorAll('.fosrev-modal__slider__i');
                sliderItems.forEach(function(item) {
                  item.classList.remove('fosrev-modal__slider__i_active');
                });
                var activeSliderItem = document.querySelector('.fosrev-modal__slider__i[data-counter="' + counter + '"]');
                activeSliderItem.classList.add('fosrev-modal__slider__i_active');

                var modalElement = document.querySelector('.fosrev-modal');
                modalElement.scrollTop = 0;
              });
            }
            // arrows slide
            var slidePrev = document.querySelectorAll('.fosrev-modal__slider__prev');
            var slideNext = document.querySelectorAll('.fosrev-modal__slider__next');
            for (var i = 0; i < slidePrev.length; i++) {
              slidePrev[i].addEventListener('click', function() {
                var activeSliderItem = document.querySelector('.fosrev-modal__slider__i_active');
                var counter = parseInt(activeSliderItem.getAttribute('data-counter'));
                counter--;
                if (counter > 0) {
                  activeSliderItem.classList.remove('fosrev-modal__slider__i_active');
                  var newActiveSliderItem = document.querySelector('.fosrev-modal__slider__i[data-counter="' + counter + '"]');
                  newActiveSliderItem.classList.add('fosrev-modal__slider__i_active');
                }
              });
            }
            for (var i = 0; i < slideNext.length; i++) {
              slideNext[i].addEventListener('click', function() {
                var activeSliderItem = document.querySelector('.fosrev-modal__slider__i_active');
                var counter = parseInt(activeSliderItem.getAttribute('data-counter'));
                counter++;
                var newActiveSliderItem = document.querySelector('.fosrev-modal__slider__i[data-counter="' + counter + '"]');
                if (newActiveSliderItem) {
                  activeSliderItem.classList.remove('fosrev-modal__slider__i_active');
                  newActiveSliderItem.classList.add('fosrev-modal__slider__i_active');
                }
              });
            }
            // close
            var closeModal = document.querySelectorAll('.fosrev-modal__close');
            for (var i = 0; i < closeModal.length; i++) {
              closeModal[i].addEventListener('click', function() {
                document.body.classList.remove("fos-reviews-body-hidden");
                var modalFosRev = document.getElementById('modalFosRevPhotos');
                modalFosRev.style.display = 'none';
              });
            }
          }
        };
        xhr.send();
      }
    }
    function scrollToReview(reviewId) {
      if(reviewId !== false) {
        var reviewElement = document.querySelector('.fosrev-modal__list-reviews__i[data-id="' + reviewId + '"]');
        if(reviewElement) {
          reviewElement.scrollIntoView({ behavior: 'smooth' });
        }
      }
    }
    function getModalReviews(reviewId) {
      var modalFosAllRev = document.getElementById('modalAllReviews');
      if(modalFosAllRev !== null) {
        modalFosAllRev.style.display = 'flex';
        document.body.classList.add("fos-reviews-body-hidden");
        scrollToReview(reviewId);
      } else {
        var xhr = new XMLHttpRequest();
        xhr.open('GET', '/bitrix/tools/fosreviews/getAllReviews.php', true);
        xhr.onreadystatechange = function() {
          if (xhr.readyState === XMLHttpRequest.DONE && xhr.status === 200) {
            var template = xhr.responseText;
              
            var modalContainer = document.createElement('div');
            modalContainer.innerHTML = template;
            document.body.appendChild(modalContainer);

            document.body.classList.add("fos-reviews-body-hidden");
                
            // Add event close
            var btnClose = document.querySelectorAll('.fosrev-modal__close');
            for (var i = 0; i < btnClose.length; i++) {
              btnClose[i].addEventListener('click', function() {
                document.body.classList.remove("fos-reviews-body-hidden");
                var modalFosAllRev = document.getElementById('modalAllReviews');
                modalFosAllRev.style.display = 'none';
              });
            }

            // Photos
            var reviewElements = document.querySelectorAll('.fos-review-photos__i_revmod');
            if(reviewElements.length > 0) {
              for (var i = 0; i < reviewElements.length; i++) {
                reviewElements[i].addEventListener('click', function() {
                  let photoId = this.getAttribute('data-id');
                  getModalPhotos(photoId);
                });
              }
            }
            scrollToReview(reviewId);
          }
        }
        xhr.send();
      }
    }
    /* All
    ************************************************************************************************************************************************************************************
    ************************************************************************************************************************************************************************************/
    const parentElement = document.body;
    var sliderContainer = document.querySelector('.fos-reviews__slider-container');
    var prevButton = document.querySelector('.fos-reviews__slider-controls__prev-button');
    var nextButton = document.querySelector('.fos-reviews__slider-controls__next-button');
    if(sliderContainer) {
      var slideItems = document.querySelectorAll('.fos-reviews__slide');
      var slideWidth = slideItems[0].offsetWidth;
      var containerWidth = sliderContainer.offsetWidth;
      var position = 0;
      var maxPosition = -(slideWidth * slideItems.length) + containerWidth;
      var currentTranslate = 0;
      var prevTranslate = 0;

      function toggleNavigationButtons() {
          prevButton.style.display = position < 0 ? 'flex' : 'none';
          nextButton.style.display = position > maxPosition ? 'flex' : 'none';
      }
      function setPositionByIndex(index) {
          position = -index * slideWidth;
          currentTranslate = position;
          prevTranslate = position;
          sliderContainer.style.transition = 'transform 0.5s';
          sliderContainer.style.transform = 'translateX(' + position + 'px)';
      }
      prevButton.addEventListener('click', function() {
          if (position < 0) {
            position += slideWidth;
            setPositionByIndex(Math.abs(Math.floor(position / slideWidth)));
            toggleNavigationButtons();
          }
      });
      nextButton.addEventListener('click', function() {
          if (position > maxPosition) {
            position -= slideWidth;
            setPositionByIndex(Math.abs(Math.floor(position / slideWidth)));
            toggleNavigationButtons();
          }
      });
      window.addEventListener('resize', function() {
          slideWidth = slideItems[0].offsetWidth;
          containerWidth = sliderContainer.offsetWidth;
          maxPosition = -(slideWidth * slideItems.length) + containerWidth;
          setPositionByIndex(Math.abs(Math.floor(position / slideWidth)));
          toggleNavigationButtons();
      });
      toggleNavigationButtons();
    }
    /* Photos modal
    ************************************************************************************************************************************************************************************
    ************************************************************************************************************************************************************************************/
    var sliderElements = document.querySelectorAll('.fos-reviews__photos-list__i');
    var sliderElementsBg = document.querySelectorAll('.fos-reviews__slide__bg-all');
    var reviewElements = document.querySelectorAll('.fos-review-photos__i');

    if(sliderElements.length > 0) {
      for (var i = 0; i < sliderElements.length; i++) {
        sliderElements[i].addEventListener('click', function() {
          let photoId = this.getAttribute('data-id');
          getModalPhotos(photoId);
        });
      }
      if(sliderElementsBg.length > 0) {
        for (var i = 0; i < sliderElementsBg.length; i++) {
          sliderElementsBg[i].addEventListener('click', function() {
            let photoId = this.getAttribute('data-id');
            getModalPhotos(photoId);
          });
        }
      }
    }
    if(reviewElements.length > 0) {
      for (var i = 0; i < reviewElements.length; i++) {
        reviewElements[i].addEventListener('click', function() {
          let photoId = this.getAttribute('data-id');
          getModalPhotos(photoId);
        });
      }
    }
    /* Review modal
    ************************************************************************************************************************************************************************************
    ************************************************************************************************************************************************************************************/
    var btnRev = document.querySelectorAll('.fos-reviews__btn-form_snd');
    if(btnRev.length > 0) {
      for (var i = 0; i < btnRev.length; i++) {
        btnRev[i].addEventListener('click', function() {
          var modalFosRev = document.getElementById('modalFosRevReviews');
          if(modalFosRev !== null) {
            modalFosRev.style.display = 'flex';
            document.body.classList.add("fos-reviews-body-hidden");
          } else {
            var xhr = new XMLHttpRequest();
            xhr.open('GET', '/bitrix/tools/fosreviews/getForm.php', true);
            xhr.onreadystatechange = function() {
              if (xhr.readyState === XMLHttpRequest.DONE && xhr.status === 200) {
                var template = xhr.responseText;
              
                var modalContainer = document.createElement('div');
                modalContainer.innerHTML = template;
                document.body.appendChild(modalContainer);

                document.body.classList.add("fos-reviews-body-hidden");
                
                // Add event close
                var btnClose = document.querySelectorAll('.fosrev-modal-mini__close');
                for (var i = 0; i < btnClose.length; i++) {
                  btnClose[i].addEventListener('click', function() {
                    document.body.classList.remove("fos-reviews-body-hidden");
                    var modalFosRev = document.getElementById('modalFosRevReviews');
                    modalFosRev.style.display = 'none';
                  });
                }
                
                var inputs = [];
                inputs.push(document.getElementById('fosrev-modal-mini__content__photos__input1'));
                inputs.push(document.getElementById('fosrev-modal-mini__content__photos__input2'));
                inputs.push(document.getElementById('fosrev-modal-mini__content__photos__input3'));
                inputs.push(document.getElementById('fosrev-modal-mini__content__photos__input4'));
                inputs.push(document.getElementById('fosrev-modal-mini__content__photos__input5'));

                var photosContainer = document.querySelector('.fosrev-modal-mini__content__photos__items');

                for (let m = 0; m < inputs.length; m++) {
                  inputs[m].addEventListener('change', handleFileSelect, false);
                  function handleFileSelect(event) {
                    var files = event.target.files;
                
                    for (let i = 0; i < files.length; i++) {
                      var file = files[i];
                      var reader = new FileReader();
                
                      reader.onload = function(e) {
                          var imgElement = document.createElement('img');
                          imgElement.src = e.target.result;
                          imgElement.className = 'fosrev-modal-mini__content__photos__items__i__img';
                
                          var deleteDiv = document.createElement('div');
                          deleteDiv.className = 'fosrev-modal-mini__content__photos__items__i__delete';
                
                          var containerDiv = document.createElement('div');
                          containerDiv.className = 'fosrev-modal-mini__content__photos__items__i';
                          containerDiv.appendChild(imgElement);
                          containerDiv.appendChild(deleteDiv);
                
                          photosContainer.appendChild(containerDiv);
        
                          deleteDiv.addEventListener('click', function () {
                            photosContainer.removeChild(containerDiv);
                            inputs[m].value = '';
                            for (let b = 0; b < inputs.length; b++) {
                              inputs[b].parentElement.classList.remove('fosrev-modal-mini__content__photos__label_show');
                            }
                            for(let n = 0; n < inputs.length; n++) {
                              if(!inputs[n].value) {
                                inputs[n].parentElement.classList.add('fosrev-modal-mini__content__photos__label_show');
                                return;
                              }
                            }
                          });
                      };
                      reader.readAsDataURL(file);
                      inputs[m].parentElement.classList.remove('fosrev-modal-mini__content__photos__label_show');
                      for(let n = 0; n < inputs.length; n++) {
                        if(!inputs[n].value) {
                          inputs[n].parentElement.classList.add('fosrev-modal-mini__content__photos__label_show');
                          return;
                        }
                      }
                    }
                  }
                }

                var formBtn = document.getElementById('fosrev-send-btn');
                formBtn.addEventListener('click', function(event) {
                  event.preventDefault();
                  var rating = document.querySelector('.fosrev-modal-mini__content__star__i_active').getAttribute('data-star');
                  var message = document.querySelector('.fosrev-modal-mini__content__message__inp').value;
                  var photoInputs = Array.from(document.querySelectorAll('.fosrev-modal-mini__content__photos__input'));
                  var photoFiles = photoInputs.map(input => input.files[0]).filter(file => file);

                  var formData = new FormData();
                  formData.append('rating', rating);
                  formData.append('message', message);
                  photoFiles.forEach((file, index) => {
                      formData.append(`photo${index + 1}`, file);
                  });

                  var xhrs = new XMLHttpRequest();
                  xhrs.open('POST', '/bitrix/tools/fosreviews/addComment.php', true);
                  xhrs.onload = function() {
                      if (xhrs.status >= 200 && xhrs.status < 300) {
                          var template = xhrs.responseText;
                          document.getElementById('fosrev-send-replace').innerHTML = template;
                      }
                  };
                  xhrs.send(formData);
                });
              }
            }
            xhr.send();
          }
        });
      }
    }
    parentElement.addEventListener('click', function(event) {
        if(event.target.matches('.fosrev-modal-mini__content__star__i')) {
            var starItems = document.querySelectorAll('.fosrev-modal-mini__content__star__i');
            var sendButton = document.querySelector('.fosrev-modal-mini__content__photos__but');
            starItems.forEach(item => {
                item.classList.remove('fosrev-modal-mini__content__star__i_active');
            });
            event.target.classList.add('fosrev-modal-mini__content__star__i_active');
            sendButton.removeAttribute('disabled');
        }
    });
    /* List reviews
    ************************************************************************************************************************************************************************************
    ************************************************************************************************************************************************************************************/
    var sliderListContainer = document.querySelector('.fos-reviews__list__slider__container');
    var prevListButton = document.querySelector('.fos-reviews__list__slider-controls__prev-button');
    var nextListButton = document.querySelector('.fos-reviews__list__slider-controls__next-button');

    if(sliderListContainer) {
      var slideItemsList = document.querySelectorAll('.fos-review');
      var slideWidthList = slideItemsList[0].offsetWidth;
      var containerWidthList = sliderListContainer.offsetWidth;
      var positionList = 0;
      var maxPositionList = -(slideWidthList * slideItemsList.length) + containerWidthList;

      function toggleNavigationButtonsList() {
          prevListButton.style.display = positionList < 0 ? 'flex' : 'none';
          nextListButton.style.display = positionList > maxPositionList ? 'flex' : 'none';
      }
      function setPositionByIndexList(index) {
          positionList = -index * slideWidthList;
          currentTranslateList = positionList;
          prevTranslateList = positionList;
          sliderListContainer.style.transition = 'transform 0.5s';
          sliderListContainer.style.transform = 'translateX(' + positionList + 'px)';
      }
      prevListButton.addEventListener('click', function() {
          if (positionList < 0) {
            positionList += slideWidthList;
            setPositionByIndexList(Math.abs(Math.floor(positionList / slideWidthList)));
            toggleNavigationButtonsList();
          }
      });
      nextListButton.addEventListener('click', function() {
          if (positionList > maxPositionList) {
            positionList -= slideWidthList;
            setPositionByIndexList(Math.abs(Math.floor(positionList / slideWidthList)));
            toggleNavigationButtonsList();
          }
      });
        
      toggleNavigationButtonsList();
    }
    /* All reviews
    ************************************************************************************************************************************************************************************
    ************************************************************************************************************************************************************************************/
    var miniRev = document.querySelectorAll('.fos-review__wrap');
    if(miniRev.length > 0) {
      for (var i = 0; i < miniRev.length; i++) {
        miniRev[i].addEventListener('click', function(event) {
          if (
              event.target.classList.contains('fos-review-photos__i') ||
              event.target.classList.contains('fos-review-photos__img') ||
              event.target.parentElement.classList.contains('fos-review-photos__i')
          ) {
              return;
          }
          let reviewId = this.getAttribute('data-id');
          getModalReviews(reviewId);
        });
      }
    }
    var allReviews = document.querySelector('.fos-reviews__btn-all');
    if(allReviews) {
      allReviews.addEventListener('click', function(event) {
        event.preventDefault();
        getModalReviews(false);
      });
    }
});
  