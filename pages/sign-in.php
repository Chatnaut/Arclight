<?php
// If the SESSION has not been started, start it now
if (!isset($_SESSION)) {
  session_start();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link rel="apple-touch-icon" sizes="76x76" href="../assets/img/favicon.png" />
  <link rel="icon" type="image/png" href="../assets/img/favicon.png" />
  <title>Arclight Dashboard - Login Page</title>
  <!-- Fonts and icons -->
  <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700" rel="stylesheet" />
  <!-- Font Awesome Icons -->
  <script src="https://kit.fontawesome.com/42d5adcbca.js" crossorigin="anonymous"></script>
  <!-- Nucleo Icons -->
  <link href="../assets/css/nucleo-icons.css" rel="stylesheet" />
  <link href="../assets/css/nucleo-svg.css" rel="stylesheet" />
  <!-- Main Styling -->
  <link href="../assets/css/styles.css?v=1.0.2" rel="stylesheet" />
  <style>
    .api-status-dot {
      display: inline-block;
      width: 5px;
      height: 5px;
      vertical-align: 9px;
      pointer-events: none;
      border-radius: 50%;
      /* background-color: #a9a9a9; */
    }
  </style>
</head>

<body class="m-0 font-sans antialiased font-normal bg-white text-start text-size-base leading-default text-slate-500">

  <div class="container sticky top-0 z-sticky">
    <div class="flex flex-wrap -mx-3">
      <div class="w-full max-w-full px-3 flex-0">
        <!-- Navbar -->
        <nav class="absolute top-0 left-0 right-0 z-30 flex flex-wrap items-center px-4 py-2 mx-0 my-0 shadow-soft-2xl bg-white/80 backdrop-blur-2xl backdrop-saturate-200 lg:flex-nowrap lg:justify-start">
          <div class="flex items-center justify-between w-full p-0 pl-6 mx-auto flex-wrap-inherit">
          <img src="../assets/img/arclight-light.svg" class="mr-3 h-6 sm:h-9" alt="arclight Logo" />
<!-- 
            <a class="py-2.375 text-size-sm mr-4 ml-4 whitespace-nowrap font-bold text-slate-700 lg:ml-0" href="/arclight"> Arclight </a> -->
            <button navbar-trigger class="px-3 py-1 ml-2 leading-none transition-all bg-transparent border border-transparent border-solid rounded-lg shadow-none cursor-pointer text-size-lg ease-soft-in-out lg:hidden" type="button" aria-controls="navigation" aria-expanded="false" aria-label="Toggle navigation">
              <span class="inline-block mt-2 align-middle bg-center bg-no-repeat bg-cover w-6-em h-6-em bg-none">
                <span bar1 class="w-5.5 rounded-xs relative my-0 mx-auto block h-px bg-gray-600 transition-all duration-300"></span>
                <span bar2 class="w-5.5 rounded-xs mt-1.75 relative my-0 mx-auto block h-px bg-gray-600 transition-all duration-300"></span>
                <span bar3 class="w-5.5 rounded-xs mt-1.75 relative my-0 mx-auto block h-px bg-gray-600 transition-all duration-300"></span>
              </span>
            </button>
            <div navbar-menu class="items-center flex-grow overflow-hidden transition-all duration-500 ease-soft lg-max:max-h-0 basis-full lg:flex lg:basis-auto">
              <ul class="flex flex-col pl-0 mx-auto mb-0 list-none lg:flex-row xl:ml-auto">
                <li>
                  <a class="flex items-center px-4 py-2 mr-2 font-normal transition-all lg-max:opacity-0 duration-250 ease-soft-in-out text-size-sm text-slate-700 lg:px-2" aria-current="page" href="../pages/sign-up.php">
                    <i class="mr-1 fas fa-user-circle opacity-60"></i>
                    Sign up
                  </a>
                </li>
                <li>
                  <a class="block px-4 py-2 mr-2 font-normal transition-all lg-max:opacity-0 duration-250 ease-soft-in-out text-size-sm text-slate-700 lg:px-2" href="../pages/profile.html">
                    <i class="mr-1 fa fa-user opacity-60"></i>
                    Profile
                  </a>
                </li>
                <li>
                  <a class="block px-4 py-2 mr-2 font-normal transition-all lg-max:opacity-0 duration-250 ease-soft-in-out text-size-sm text-slate-700 lg:px-2">
                    <i class="mr-1 fa fa-chart-pie opacity-60"></i>
                    API Status
                    <span class="api-status-dot"></span></a>
                  <div class="z-50 hidden px-2 py-1 text-center text-white bg-black rounded-lg max-w-46 text-size-sm" id="tooltip" role="tooltip" data-popper-placement="bottom">
                    My tooltip
                    <div id="arrow" class="invisible absolute h-2 w-2 bg-inherit before:visible before:absolute before:h-2 before:w-2 before:rotate-45 before:bg-inherit before:content-['']" data-popper-arrow></div>
                  </div>
                </li>
                <li>
                  <a class="block px-4 py-2 mr-2 font-normal transition-all lg-max:opacity-0 duration-250 ease-soft-in-out text-size-sm text-slate-700 lg:px-2" href="../pages/sign-in.php">
                    <i class="mr-1 fas fa-key opacity-60"></i>
                    Sign In
                  </a>
                </li>
                <ul class="hidden pl-0 mb-0 list-none lg:block lg:flex-row">
                  <li>
                    <a class="leading-pro active:opacity-85 ease-soft-in text-size-xs tracking-tight-soft rounded-3.5xl mb-0 mr-1 inline-block border-0 bg-transparent px-8 py-2 text-center align-middle font-bold uppercase text-white transition-all"></a>
                  </li>
                </ul>
            </div>
          </div>
        </nav>
      </div>
    </div>
  </div>
  <main class="mt-0 transition-all duration-200 ease-soft-in-out">
    <section>
      <div class="relative flex items-center p-0 overflow-hidden bg-center bg-cover min-h-75-screen">
        <div class="container z-10">
          <div class="flex flex-wrap mt-0 -mx-3">
            <div class="flex flex-col w-full max-w-full px-3 mx-auto md:flex-0 shrink-0 md:w-6/12 lg:w-5/12 xl:w-4/12">
              <div class="relative flex flex-col min-w-0 mt-32 break-words bg-transparent border-0 shadow-none rounded-2xl bg-clip-border">
                <div class="p-6 pb-0 mb-0 bg-transparent border-b-0 rounded-t-2xl">
                  <h3 class="relative z-10 font-bold text-transparent bg-gradient-cyan bg-clip-text">Welcome back</h3>
                  <p class="mb-0">Enter your email and password to sign in</p>
                </div>
                <div class="flex-auto p-6">
                  <form class="form-signin" method="" action="">
                    <label class="mb-2 ml-1 font-bold text-size-xs text-slate-700">Email</label>
                    <div class="mb-4">
                      <input type="email" class="focus:shadow-soft-primary-outline text-size-sm leading-5.6 ease-soft block w-full appearance-none rounded-lg border border-solid border-gray-300 bg-white bg-clip-padding px-3 py-2 font-normal text-gray-700 transition-all focus:border-fuchsia-300 focus:outline-none focus:transition-shadow" name="email" id="inputEmail" placeholder="Email" aria-label="Email" aria-describedby="email-addon" required autofocus />
                    </div>
                    <label class="mb-2 ml-1 font-bold text-size-xs text-slate-700">Password</label>
                    <div class="mb-4">
                      <input type="password" class="focus:shadow-soft-primary-outline text-size-sm leading-5.6 ease-soft block w-full appearance-none rounded-lg border border-solid border-gray-300 bg-white bg-clip-padding px-3 py-2 font-normal text-gray-700 transition-all focus:border-fuchsia-300 focus:outline-none focus:transition-shadow" name="password" id="inputPassword" placeholder="Password" aria-label="Password" aria-describedby="password-addon" required autofocus />
                    </div>
                    <div class="min-h-6 mb-0.5 block pl-12">
                      <input id="rememberMe" class="mt-0.54 rounded-10 duration-250 ease-soft-in-out after:rounded-circle after:shadow-soft-2xl after:duration-250 checked:after:translate-x-5.25 h-5-em relative float-left -ml-12 w-10 cursor-pointer appearance-none border border-solid border-gray-200 bg-slate-800/10 bg-none bg-contain bg-left bg-no-repeat align-top transition-all after:absolute after:top-px after:h-4 after:w-4 after:translate-x-px after:bg-white after:content-[''] checked:border-slate-800/95 checked:bg-slate-800/95 checked:bg-none checked:bg-right" type="checkbox" checked="" />
                      <label class="mb-2 ml-1 font-normal cursor-pointer select-none text-size-sm text-slate-700" for="rememberMe">Remember me</label>

                    </div>
                    <!-- error message if any-->
                    <div class="text-red-500 text-sm italic">
                      <p id="error-message"></p>
                    </div>

                    <div class="text-center">
                      <button type="submit" class="inline-block w-full px-6 py-3 mt-6 mb-2 font-bold text-center text-white uppercase align-middle transition-all bg-transparent border-0 rounded-lg cursor-pointer active:opacity-85 hover:scale-102 hover:shadow-soft-xs leading-pro text-size-xs ease-soft-in tracking-tight-soft shadow-soft-md bg-150 bg-x-25 bg-gradient-dark-gray hover:border-slate-700 hover:bg-slate-700 hover:text-white">Sign
                        in</button>
                    </div>
                  </form>
                </div>
                <div class="p-6 px-1 pt-0 text-center bg-transparent border-t-0 border-t-solid rounded-b-2xl lg:px-2">
                  <p class="mx-auto mb-6 leading-normal text-size-sm">
                    Don't have an account?
                    <a href="../pages/sign-up.php" class="relative z-10 font-semibold text-transparent bg-gradient-cyan bg-clip-text">Sign up</a>
                  </p>
                </div>
              </div>
            </div>
            <div class="w-full max-w-full px-3 lg:flex-0 shrink-0 md:w-6/12">
              <div class="absolute top-0 hidden w-3/5 h-full -mr-32 overflow-hidden -skew-x-10 -right-40 rounded-bl-xl md:block">
                <div class="absolute inset-x-0 top-0 z-0 h-full -ml-16 bg-cover skew-x-10" style="background-image: url('../assets/img/arclightbg.jpg')"></div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>
  </main>
  <footer class="py-12">
    <div class="container">
      <div class="flex flex-wrap -mx-3">
        <div class="flex-shrink-0 w-full max-w-full mx-auto mb-6 text-center lg:flex-0 lg:w-8/12">
          <!-- <a href="javascript:;" target="_blank" class="mb-2 mr-4 text-slate-400 sm:mb-0 xl:mr-12"> Company </a>
          <a href="javascript:;" target="_blank" class="mb-2 mr-4 text-slate-400 sm:mb-0 xl:mr-12"> About Us </a>
          <a href="javascript:;" target="_blank" class="mb-2 mr-4 text-slate-400 sm:mb-0 xl:mr-12"> Team </a>
          <a href="javascript:;" target="_blank" class="mb-2 mr-4 text-slate-400 sm:mb-0 xl:mr-12"> Products </a>
          <a href="javascript:;" target="_blank" class="mb-2 mr-4 text-slate-400 sm:mb-0 xl:mr-12"> Blog </a>
          <a href="javascript:;" target="_blank" class="mb-2 mr-4 text-slate-400 sm:mb-0 xl:mr-12"> Pricing </a> -->
        </div>
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
    const formDOM = document.querySelector('.form-signin');
    const emailInputDOM = document.querySelector('#inputEmail');
    const passwordInputDOM = document.querySelector('#inputPassword');
    const error = document.querySelector('#error-message');
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
        error.innerHTML = "Error: Arc api not running";
        dotapi.style.backgroundColor = "#a9a9a9";
      });

    formDOM.addEventListener('submit', async (e) => {
      e.preventDefault();
      const email = emailInputDOM.value;
      const password = passwordInputDOM.value;
      try {
        const {
          data
        } = await axios.post(`/api/v1/auth/login`, {
          email,
          password
        });
        if (data.success == 1) {
          localStorage.setItem('token', data.token);
          localStorage.setItem('userid', data.user._id);
          localStorage.setItem('username', data.user.username);
          localStorage.setItem('email', data.user.email);
          localStorage.setItem('role', data.user.role);
          setConfigSession();
        } else {
          console.log(data.message);
          error.innerHTML = data.message;
        }
      } catch (err) {
        localStorage.removeItem('token');
        localStorage.removeItem('userid');
        localStorage.removeItem('username');
        localStorage.removeItem('email');
        localStorage.removeItem('role');
        error.innerHTML = err.response.data.message;
      }
    });
    const setConfigSession = async () => {
      const token = localStorage.getItem('token')
      const userid = localStorage.getItem('userid')
      const username = localStorage.getItem('username')
      try {
        const {
          data
        } = await axios.get(`/api/v1/config/arc_config/${userid}`, {
          headers: {
            Authorization: `Bearer ${token}`,
          },
        })
        //iterate through array and set the config session
        data.result.forEach(element => {
          if (element.name == "theme_color") {
            theme_color = element.value;
          }else{
            theme_color = "white";
          }
          if (element.name == "language") {
            language = element.value;
          }else{
            language = "english";
          }
        });
        //sending data to store in php session
        axios.post('sessions.php', {
          userid: userid,
          username: username,
          theme_color: theme_color,
          language: language
        }).then(function(response) {
          window.location.href = '../index.php';
        })
        console.log(data.message);
      } catch (err) {
        //call setDefaultSession function
        console.log(err);
        setDefaultSession();
      }
    }
    const setDefaultSession = async () => {
      try {
        axios.post('sessions.php', {
          userid: localStorage.getItem('userid'),
          username: localStorage.getItem('username'),
          email: localStorage.getItem('email'),
          role: localStorage.getItem('role'),
          theme_color: "white",
          language: "english"
        }).then(function(response) {
          window.location.href = '../index.php';
        })
      } catch (err) {
        console.log(err);
      }
    }
  </script>
</body>
<script src="../assets/js/plugins/perfect-scrollbar.min.js" async></script>
</html>