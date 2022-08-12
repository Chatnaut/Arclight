<?php include('auth_header.php'); ?>

<body class="m-0 font-sans antialiased font-normal bg-white text-start text-size-base leading-default text-slate-500">
  <!-- Navbar -->
  <nav class="absolute top-0 z-30 flex flex-wrap items-center justify-between w-full px-4 py-2 mt-6 mb-4 shadow-none lg:flex-nowrap lg:justify-start">
    <div class="container flex items-center justify-between py-0 flex-wrap-inherit">
      <img src="../assets/img/arclight-dark.svg" class="mr-3 h-6 sm:h-9" alt="arclight Logo" />
      <button navbar-trigger class="px-3 py-1 ml-2 leading-none transition-all bg-transparent border border-transparent border-solid rounded-lg shadow-none cursor-pointer text-size-lg ease-soft-in-out lg:hidden" type="button" aria-controls="navigation" aria-expanded="false" aria-label="Toggle navigation">
        <span class="inline-block mt-2 align-middle bg-center bg-no-repeat bg-cover w-6-em h-6-em bg-none">
          <span bar1 class="w-5.5 rounded-xs duration-350 relative my-0 mx-auto block h-px bg-white transition-all"></span>
          <span bar2 class="w-5.5 rounded-xs mt-1.75 duration-350 relative my-0 mx-auto block h-px bg-white transition-all"></span>
          <span bar3 class="w-5.5 rounded-xs mt-1.75 duration-350 relative my-0 mx-auto block h-px bg-white transition-all"></span>
        </span>
      </button>
      <div navbar-menu class="items-center flex-grow transition-all ease-soft duration-350 lg-max:bg-white lg-max:max-h-0 lg-max:overflow-hidden basis-full rounded-xl lg:flex lg:basis-auto">
        <ul class="flex flex-col pl-0 mx-auto mb-0 list-none lg:flex-row xl:ml-auto">
          <li>
            <a class="block px-4 py-2 mr-2 font-normal text-white transition-all duration-250 lg-max:opacity-0 lg-max:text-slate-700 ease-soft-in-out text-size-sm lg:px-2 lg:hover:text-white/75" href="../pages/sign-up.php">
              <i class="mr-1 text-white lg-max:text-slate-700 fas fa-user-circle opacity-60"></i>
              Sign Up
            </a>
          </li>
          <li>
            <a class="block px-4 py-2 mr-2 font-normal text-white transition-all duration-250 lg-max:opacity-0 lg-max:text-slate-700 ease-soft-in-out text-size-sm lg:px-2 lg:hover:text-white/75" href="../pages/profile.html">
              <i class="mr-1 text-white lg-max:text-slate-700 fa fa-user opacity-60"></i>
              Profile
            </a>
          </li>
          <li>
            <a class="block px-4 py-2 mr-2 font-normal text-white transition-all duration-250 lg-max:opacity-0 lg-max:text-slate-700 ease-soft-in-out text-size-sm lg:px-2 lg:hover:text-white/75">
              <i class="mr-1 fa fa-chart-pie opacity-60"></i>
              API Status
              <span class="api-status-dot"></span></a>
            <div class="z-50 hidden px-2 py-1 text-center text-white bg-black rounded-lg max-w-46 text-size-sm" id="tooltip" role="tooltip" data-popper-placement="bottom">
              My tooltip
              <div id="arrow" class="invisible absolute h-2 w-2 bg-inherit before:visible before:absolute before:h-2 before:w-2 before:rotate-45 before:bg-inherit before:content-['']" data-popper-arrow></div>
            </div>
          </li>
          <li>
          <li>
            <a class="block px-4 py-2 mr-2 font-normal text-white transition-all duration-250 lg-max:opacity-0 lg-max:text-slate-700 ease-soft-in-out text-size-sm lg:px-2 lg:hover:text-white/75" href="../pages/sign-in.php">
              <i class="mr-1 text-white lg-max:text-slate-700 fas fa-key opacity-60"></i>
              Sign In
            </a>
          </li>
        </ul>
        <ul class="hidden pl-0 mb-0 list-none lg:block lg:flex-row">
          <li>
            <a class="leading-pro active:opacity-85 ease-soft-in text-size-xs tracking-tight-soft rounded-3.5xl mb-0 mr-1 inline-block border-0 bg-transparent px-8 py-2 text-center align-middle font-bold uppercase text-white transition-all"></a>
          </li>
        </ul>
      </div>
    </div>
  </nav>
  <main class="mt-0 transition-all duration-200 ease-soft-in-out">
    <section class="min-h-screen mb-32">
      <div class="relative flex items-start pt-12 pb-56 m-4 overflow-hidden bg-center bg-cover min-h-50-screen rounded-xl" style="background-image: url('../assets/img/signupclip.jpg')">
        <span class="absolute top-0 left-0 w-full h-full bg-center bg-cover bg-gradient-dark-gray opacity-60"></span>
        <div class="container z-10">
          <div class="flex flex-wrap justify-center -mx-3">
            <div class="w-full max-w-full px-3 mx-auto mt-0 text-center lg:flex-0 shrink-0 lg:w-5/12">
              <h1 class="mt-12 mb-2 text-white">Welcome To!</h1>
              <p class="text-white">Arclight Console Dashboard</p>
              <div class="error-messages">
                <ul class="messages">
                </ul>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="container">
        <div class="flex flex-wrap -mx-3 -mt-48 md:-mt-56 lg:-mt-48">
          <div class="w-full max-w-full px-3 mx-auto mt-0 md:flex-0 shrink-0 md:w-7/12 lg:w-5/12 xl:w-4/12">
            <div class="relative z-0 flex flex-col min-w-0 break-words bg-white border-0 shadow-soft-xl rounded-2xl bg-clip-border">
              <div class="p-6 mb-0 text-center bg-white border-b-0 rounded-t-2xl">
                <h5>Register as a New User</h5>
              </div>
               <div class="flex-auto p-6">
                <form role="form text-left" action="/api/v1/auth/register" method="post" class="form-signup">
                  <div class="mb-4">
                    <input type="text" class="text-size-sm focus:shadow-soft-primary-outline leading-5.6 ease-soft block w-full appearance-none rounded-lg border border-solid border-gray-300 bg-white bg-clip-padding py-2 px-3 font-normal text-gray-700 transition-all focus:border-fuchsia-300 focus:bg-white focus:text-gray-700 focus:outline-none focus:transition-shadow" placeholder="Name" aria-label="Name" aria-describedby="email-addon" id="name" name="name" />
                  </div>
                  <div class="mb-4">
                    <input type="email" class="text-size-sm focus:shadow-soft-primary-outline leading-5.6 ease-soft block w-full appearance-none rounded-lg border border-solid border-gray-300 bg-white bg-clip-padding py-2 px-3 font-normal text-gray-700 transition-all focus:border-fuchsia-300 focus:bg-white focus:text-gray-700 focus:outline-none focus:transition-shadow" placeholder="Email Address" aria-label="Email" aria-describedby="email-addon" id="email" name="email" />
                  </div>
                  <div class="mb-4">
                    <input type="password" class="text-size-sm focus:shadow-soft-primary-outline leading-5.6 ease-soft block w-full appearance-none rounded-lg border border-solid border-gray-300 bg-white bg-clip-padding py-2 px-3 font-normal text-gray-700 transition-all focus:border-fuchsia-300 focus:bg-white focus:text-gray-700 focus:outline-none focus:transition-shadow" placeholder="Create a password" aria-label="Password" aria-describedby="password-addon" id="password" name="password" />
                  </div>
                    <div class="text-center">
                    <button type="submit" class="inline-block w-full px-6 py-3 mt-6 mb-2 font-bold text-center text-white uppercase align-middle transition-all bg-transparent border-0 rounded-lg cursor-pointer active:opacity-85 hover:scale-102 hover:shadow-soft-xs leading-pro text-size-xs ease-soft-in tracking-tight-soft shadow-soft-md bg-150 bg-x-25 bg-gradient-dark-gray hover:border-slate-700 hover:bg-slate-700 hover:text-white">Sign
                      up</button>
                  </div>
                  <p class="mt-4 mb-0 leading-normal text-size-sm">Already have an account? <a href="../pages/sign-in.php" class="font-bold text-slate-700">Sign in</a></p>
                </form>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>
  </main>
  <!-- -------- START FOOTER 3 w/ COMPANY DESCRIPTION WITH LINKS & SOCIAL ICONS & COPYRIGHT ------- -->
  <?php include 'auth_footer.php'; ?>
  <!-- -------- END FOOTER 3 w/ COMPANY DESCRIPTION WITH LINKS & SOCIAL ICONS & COPYRIGHT ------- -->

  <script>
    const formDOM = document.querySelector('.form-signup');
    const nameInputDOM = document.querySelector('#name');
    const emailInputDOM = document.querySelector('#email');
    const passwordInputDOM = document.querySelector('#password');
    const flashMessages = document.querySelector('.messages');
    const dotapi = document.querySelector('.api-status-dot');

    //get arc api health status
    axios.get(`/api/v1/status/health`)
      .then(function(response) {
        if (response.status == 200) {
          console.log("Arc api is healthy");
          dotapi.style.backgroundColor = "#3cb46e";
        } else {
          console.log("Arc api is not healthy");
          dotapi.style.backgroundColor = "#ff0000";
        }
      })
      .catch(function(err) {
        console.log("Arc api is not healthy");
        dotapi.style.backgroundColor = "#a9a9a9";
        flashMessages.innerHTML += `<li class="error">Error: Arc api not running</li><br>`;

      });

    formDOM.addEventListener('submit', (e) => {
      e.preventDefault();
      const username = nameInputDOM.value;
      const email = emailInputDOM.value;
      const password = passwordInputDOM.value;
      const data = {
        username,
        email,
        password
      };
      axios.post('/api/v1/auth/register', data)
        .then(res => {
          console.log(res.data.message);
          // window.location.href = '/pages/sign-in.php';
          getFlashMessage(res.data.message);
        })
        .catch(err => {
          console.log(err);
          getFlashMessage(err.data.message);
        });
    });
  </script>
</body>
<!-- plugin for scrollbar  -->
<script src="../assets/js/plugins/perfect-scrollbar.min.js" async></script>
<!-- main script file  -->
<script src="../assets/js/windy-tailwind.js?v=1.0.2" async></script>

</html>