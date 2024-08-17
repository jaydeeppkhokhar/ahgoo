<!DOCTYPE html>
<html lang="en">
	<!--begin::Head-->
	<head>
		<title>Login || Ahgoo Admin</title>
		<meta charset="utf-8" />
		<meta name="description" content="" />
		<meta name="keywords" content="" />
		<meta name="viewport" content="width=device-width, initial-scale=1" />
		<meta property="og:locale" content="en_US" />
		<meta property="og:type" content="article" />
		<meta property="og:title" content="Admin Login" />
		<link rel="canonical" href="https://preview.keenthemes.com/metronic8" />
		<link rel="shortcut icon" href="{{asset('assets/media/logos/favicon.ico')}}" />
		<!--begin::Fonts-->
		<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Inter:300,400,500,600,700" />
		<!--end::Fonts-->
		<!--begin::Global Stylesheets Bundle(used by all pages)-->
		<link href="{{asset('assets/plugins/global/plugins.bundle.css')}}" rel="stylesheet" type="text/css" />
		<link href="{{asset('assets/css/style.bundle.css')}}" rel="stylesheet" type="text/css" />
		<!--end::Global Stylesheets Bundle-->
	</head>
	<!--end::Head-->
	<!--begin::Body-->
	<body data-kt-name="metronic" id="kt_body" class="app-blank app-blank bgi-size-cover bgi-position-center bgi-no-repeat">
		<!--begin::Theme mode setup on page load-->
		<script>if ( document.documentElement ) { const defaultThemeMode = "system"; const name = document.body.getAttribute("data-kt-name"); let themeMode = localStorage.getItem("kt_" + ( name !== null ? name + "_" : "" ) + "theme_mode_value"); if ( themeMode === null ) { if ( defaultThemeMode === "system" ) { themeMode = window.matchMedia("(prefers-color-scheme: dark)").matches ? "dark" : "light"; } else { themeMode = defaultThemeMode; } } document.documentElement.setAttribute("data-theme", themeMode); }</script>
		<div class="d-flex flex-column flex-root" id="kt_app_root">
			<style>body { background-image: url('{{asset("assets/media/auth/bg4.jpg")}}'); } [data-theme="dark"] body { background-image: url('{{asset("assets/media/auth/bg4-dark.jpg")}}'); }</style>
			<div class="d-flex flex-column flex-column-fluid flex-lg-row">
				<div class="d-flex flex-center w-lg-50 pt-15 pt-lg-0 px-10">
					<div class="d-flex flex-column">
						<a href="{{ url('/admin/login') }}" class="mb-7">
							<img alt="Logo" src="{{asset('assets/media/logos/logo.webp')}}" />
						</a>
						<h2 class="text-white fw-normal m-0">TRANSFORMING THE WORLD ONE POST AT A TIME</h2>
					</div>
				</div>
				<div class="d-flex flex-center w-lg-50 p-10">
					<div class="card rounded-3 w-md-550px">
						<div class="card-body p-10 p-lg-20">
                            @if ($errors->any())
                                <div class="alert alert-danger">
                                    <ul>
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif
							<form class="form w-100" novalidate="novalidate" id="kt_sign_in_form" data-kt-redirect-url="{{ url('/admin/login') }}" action="{{ url('/admin/login') }}" method="post">
                            @csrf
								<div class="text-center mb-11">
									<h1 class="text-dark fw-bolder mb-3">Sign In</h1>
									@if ($errors->any())
                                        @foreach ($errors->all() as $error)
                                            <div class="text-gray-500 fw-semibold fs-6" style="color: red !important">{{ $error }}</div>
                                        @endforeach
									@endif
								</div>
								<?php /*
								<div class="row g-3 mb-9">
									<div class="col-md-6">
										<a href="#" class="btn btn-flex btn-outline btn-text-gray-700 btn-active-color-primary bg-state-light flex-center text-nowrap w-100">
										<img alt="Logo" src="<?= base_url();?>assets/media/svg/brand-logos/google-icon.svg" class="h-15px me-3" />Sign in with Google</a>
									</div>
									<div class="col-md-6">
										<a href="#" class="btn btn-flex btn-outline btn-text-gray-700 btn-active-color-primary bg-state-light flex-center text-nowrap w-100">
										<img alt="Logo" src="<?= base_url();?>assets/media/svg/brand-logos/apple-black.svg" class="theme-light-show h-15px me-3" />
										<img alt="Logo" src="<?= base_url();?>assets/media/svg/brand-logos/apple-black-dark.svg" class="theme-dark-show h-15px me-3" />Sign in with Apple</a>
									</div>
								</div> 
								<div class="separator separator-content my-14">
									<span class="w-125px text-gray-500 fw-semibold fs-7">Or with email</span>
								</div>*/?>
								<div class="fv-row mb-8">
									<input type="email" placeholder="Email" name="email" id="email" autocomplete="off" class="form-control bg-transparent" required/>
								</div>
								<div class="fv-row mb-3">
									<input type="password" placeholder="Password" name="password" id="password" autocomplete="off" class="form-control bg-transparent" required/>
								</div>
								<?php /*
								<div class="d-flex flex-stack flex-wrap gap-3 fs-base fw-semibold mb-8">
									<div></div>
									<a href="../../demo1/dist/authentication/layouts/creative/reset-password.html" class="link-primary">Forgot Password ?</a>
								</div> */?>
								<div class="d-grid mb-10">
									<button type="submit" id="kt_sign_in_submit" class="btn btn-primary">
										<span class="indicator-label">Sign In</span>
										<span class="indicator-progress">Please wait...
										<span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
									</button>
								</div>
								<?php /*
								<div class="text-gray-500 text-center fw-semibold fs-6">Not a Member yet?
								<a href="../../demo1/dist/authentication/layouts/creative/sign-up.html" class="link-primary">Sign up</a></div>*/?>
							</form>
						</div>
					</div>
				</div>
			</div>
		</div>
		<script>var hostUrl = "{{ url('/admin/login') }}assets/";</script>
		<script src="{{asset('assets/plugins/global/plugins.bundle.js')}}"></script>
		<script src="{{asset('assets/js/scripts.bundle.js')}}"></script>
	</body>
</html>
