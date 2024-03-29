<footer class="py-12">
      <div class="container">
        <div class="flex flex-wrap -mx-3">
          <!-- <div class="flex-shrink-0 w-full max-w-full mx-auto mb-6 text-center lg:flex-0 lg:w-8/12">
            <a href="javascript:;" target="_blank" class="mb-2 mr-4 text-slate-400 sm:mb-0 xl:mr-12"> Company </a>
            <a href="javascript:;" target="_blank" class="mb-2 mr-4 text-slate-400 sm:mb-0 xl:mr-12"> About Us </a>
            <a href="javascript:;" target="_blank" class="mb-2 mr-4 text-slate-400 sm:mb-0 xl:mr-12"> Team </a>
            <a href="javascript:;" target="_blank" class="mb-2 mr-4 text-slate-400 sm:mb-0 xl:mr-12"> Products </a>
            <a href="javascript:;" target="_blank" class="mb-2 mr-4 text-slate-400 sm:mb-0 xl:mr-12"> Blog </a>
            <a href="javascript:;" target="_blank" class="mb-2 mr-4 text-slate-400 sm:mb-0 xl:mr-12"> Pricing </a>
          </div> -->
          <div class="flex-shrink-0 w-full max-w-full mx-auto mt-2 mb-6 text-center lg:flex-0 lg:w-8/12">
            <a href="https://chatnaut.com/" target="_blank" class="mr-6 text-slate-400">
              <span class="text-size-lg fas fa-building"></span>
            </a>

            <a href="https://twitter.com/chatnaut" target="_blank" class="mr-6 text-slate-400">
              <span class="text-size-lg fab fa-twitter"></span>
            </a>

            <a href="https://www.instagram.com/chatnaut/" target="_blank" class="mr-6 text-slate-400">
              <span class="text-size-lg fab fa-instagram"></span>
            </a>

            <a href="https://github.com/Chatnaut" target="_blank" class="text-slate-400">
              <span class="text-size-lg fab fa-github"></span>
            </a>
          </div>
        </div>
        <div class="flex flex-wrap -mx-3">
          <div class="w-8/12 max-w-full px-3 mx-auto mt-1 text-center flex-0">
            <p class="mb-0 text-slate-400">
              Copyright &copy;
              <script>
                document.write(new Date().getFullYear());
              </script>
              Chatnaut Cloud
            </p>
          </div>
        </div>
      </div>
    </footer>
    <script src="../assets/js/windy-tailwind.js?v=1.0.2" async></script>
  <!-- <script src="https://cdn.tailwindcss.com"></script> -->
  <!-- getting axios library  -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/axios/0.21.1/axios.min.js" integrity="sha512-bZS47S7sPOxkjU/4Bt0zrhEtWx0y0CRkhEp8IckzK+ltifIIE9EMIMTuT/mEzoIMewUINruDBIR/jJnbguonqQ==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
  <script>
        function getFlashMessage(message) {
      const messageArray = Object.keys(message).forEach((key) => {
        message[key].forEach((value => {
          flashMessages.innerHTML += `<li class="${key}">${value}</li><br>`;
        }));
      })
      setTimeout(function() {
        flashMessages.innerHTML = '';
      }, 10000);
    }
  </script>