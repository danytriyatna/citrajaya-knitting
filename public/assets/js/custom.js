/**
 * This JavaScript file contains template functionality
 * to improve overall user experience (UX).
 * 
 * There are 20 functionality segments in total:
 * 
 * APP PREFIX
 * ILOADER INIT
 * TOASTR OPTIONS
 * APP DEFAULT CUSTOMIZEABLE THEME STYLE
 * THEME CUSTOMIZER
 * RESET THEME COLOR
 * CUSTOM LAYOUT
 * ENABLE BOOTSTRAP TOOLTIPS
 * ENABLE SELECT2
 * WELCOME TOAST
 * SWEETALERT DELETE CONFIRMATION
 * SWEETALERT ACTIVE CONFIRMATION
 * ECHARTS INIT
 * CKEDITOR INIT
 * DATEPICKER INIT
 * PERFECT SCROLLBAR INIT
 * DATATABLES INIT
 * FORMAT & UNFORMAT IDR CURRENCY
 * BOOTSTRAP FORM VALIDATION
 * CUSTOM INPUT FILE DRAG & DROP
 * FIX BUG BOOTSTRAP ACCORDION ON TABULATOR
 * PASSWORD VISIBILITY TOGGLE
 * NUMBER CAPTCHA (NUMCHA) GENERATOR
 * 
 * Copyright (c) 2021-2023 Agung Sulaksana <sulaksana34@gmail.com>
 */


// APP PREFIX
const appPrefix = "ci4PR";


// ILOADER INIT
const iLoaderTarget = document.querySelector(".preloader.iloader.d-none");

const iLoader = {
  start() {
    if (iLoaderTarget != null)
      iLoaderTarget.classList.replace("d-none", "d-block")
    return true
  },
  stop() {
    if (iLoaderTarget != null)
      iLoaderTarget.classList.replace("d-block", "d-none")
    return true
  }
}


// TOASTR OPTIONS
toastr.options = {
  "progressBar": true,
  "positionClass": "toast-bottom-right",
  "showDuration": "300",
  "hideDuration": "1000",
  "timeOut": "5000",
  "extendedTimeOut": "1000",
  "showEasing": "swing",
  "hideEasing": "linear",
  "showMethod": "fadeIn",
  "hideMethod": "fadeOut"
};


// APP DEFAULT CUSTOMIZEABLE THEME STYLE
const defaultColors = {
  primaryColor: "#FA003F",
	hoverColor: "#FA003FCC",
	accentColor: "#EE6123",
	bgTheadColor: "#FFCF00",
	textTheadColor: "#212529",
}

const defaultDarkColors = {
  primaryColor: "#5981EA",
	hoverColor: "#5981EACC",
	accentColor: "#5981EA",
	bgTheadColor: "#1E515E",
	textTheadColor: "#CAD6E3",
}

const rootElement = document.querySelector(":root");
let darkMode;

const updateTheme = async () => {
  iLoader.start();
  const formGroupLightColors = [...document.querySelectorAll('.form-group-light-color')]
  const formGroupDarkColors = [...document.querySelectorAll('.form-group-dark-color')]
  const colors = await fetch(
    `${BASE_URL}utilitas/setting-situs/get-colors`, {
      method: 'POST'
    })
    .then(res => res.json());

  darkMode = localStorage.getItem(`${appPrefix}DarkMode`)

  if (darkMode) {
    rootElement.setAttribute('data-bs-theme', 'dark')
    rootElement.style.cssText = `
      --primary-color: ${colors.primaryDarkColor ?? defaultDarkColors.primaryColor};
      --primary-color-hover: ${colors.primaryDarkColor ?? defaultDarkColors.primaryColor}CC;
      --accent-color: ${colors.accentDarkColor ?? defaultDarkColors.accentColor};
      --bg-thead-color: ${colors.bgTheadDarkColor ?? defaultDarkColors.bgTheadColor};
      --text-thead-color: ${colors.textTheadDarkColor ?? defaultDarkColors.textTheadColor};
    `;

    if (formGroupLightColors !== null) {
      formGroupLightColors.forEach(el => el.classList.add('d-none'));
      formGroupDarkColors.forEach(el => el.classList.remove('d-none'));
    }
  }
  else {
    rootElement.setAttribute('data-bs-theme', 'light')
    rootElement.style.cssText = `
      --primary-color: ${colors.primaryColor ?? defaultColors.primaryColor};
      --primary-color-hover: ${colors.primaryColor ?? defaultColors.primaryColor}CC;
      --accent-color: ${colors.accentColor ?? defaultColors.accentColor};
      --bg-thead-color: ${colors.bgTheadColor ?? defaultColors.bgTheadColor};
      --text-thead-color: ${colors.textTheadColor ?? defaultColors.textTheadColor};
    `;
    
    if (formGroupLightColors !== null) {
      formGroupLightColors.forEach(el => el.classList.remove('d-none'));
      formGroupDarkColors.forEach(el => el.classList.add('d-none'));
    }
  }
  iLoader.stop();
}

updateTheme();

const toggleTheme = () => {
  darkMode = localStorage.getItem(`${appPrefix}DarkMode`)

  if (darkMode) {
    localStorage.removeItem(`${appPrefix}DarkMode`)
  }
  else {
    localStorage.setItem(`${appPrefix}DarkMode`, 'true')
  }

  updateTheme();
}


// THEME CUSTOMIZER
const inputPrimaryColor = document.getElementById("theme_primary_color");
const inputAccentColor = document.getElementById("theme_accent_color");
const inputBgTheadColor = document.getElementById("theme_bg_thead_color");
const inputTextTheadColor = document.getElementById("theme_text_thead_color");
const inputPrimaryDarkColor = document.getElementById("theme_primary_dark_color");
const inputAccentDarkColor = document.getElementById("theme_accent_dark_color");
const inputBgTheadDarkColor = document.getElementById("theme_bg_thead_dark_color");
const inputTextTheadDarkColor = document.getElementById("theme_text_thead_dark_color");

const updatePerColor = (el, colorSuffix, cssVar) => {
	if (el != null) {
    el.addEventListener("change", () => {
			rootElement.style.setProperty(cssVar, el.value);
			if (colorSuffix === "PrimaryColor") {
				rootElement.style.setProperty("--primary-color-hover", `${el.value}CC`);
			}
		})
	}
}

updatePerColor(inputPrimaryColor, "PrimaryColor", "--primary-color");
updatePerColor(inputAccentColor, "AccentColor", "--accent-color");
updatePerColor(inputBgTheadColor, "BgTheadColor", "--bg-thead-color");
updatePerColor(inputTextTheadColor, "TextTheadColor", "--text-thead-color");
updatePerColor(inputPrimaryDarkColor, "PrimaryColor", "--primary-color");
updatePerColor(inputAccentDarkColor, "AccentDColor", "--accent-color");
updatePerColor(inputBgTheadDarkColor, "BgTheadColor", "--bg-thead-color");
updatePerColor(inputTextTheadDarkColor, "TextTheColor", "--text-thead-color");


// RESET THEME COLOR
const resetThemes = async () => {
  darkMode = localStorage.getItem(`${appPrefix}DarkMode`)

  if (darkMode) {
    inputPrimaryDarkColor.value = defaultDarkColors.primaryColor;
    inputAccentDarkColor.value = defaultDarkColors.accentColor;
    inputBgTheadDarkColor.value = defaultDarkColors.bgTheadColor;
    inputTextTheadDarkColor.value = defaultDarkColors.textTheadColor;

    rootElement.style.cssText = `
      --primary-color: ${defaultDarkColors.primaryColor};
      --primary-color-hover: ${defaultDarkColors.primaryColor}CC;
      --accent-color: ${defaultDarkColors.accentColor};
      --bg-thead-color: ${defaultDarkColors.bgTheadColor};
      --text-thead-color: ${defaultDarkColors.textTheadColor};
    `;
  }
  else {
    inputPrimaryColor.value = defaultColors.primaryColor;
    inputAccentColor.value = defaultColors.accentColor;
    inputBgTheadColor.value = defaultColors.bgTheadColor;
    inputTextTheadColor.value = defaultColors.textTheadColor;

    rootElement.style.cssText = `
      --primary-color: ${defaultColors.primaryColor};
      --primary-color-hover: ${defaultColors.primaryColor}CC;
      --accent-color: ${defaultColors.accentColor};
      --bg-thead-color: ${defaultColors.bgTheadColor};
      --text-thead-color: ${defaultColors.textTheadColor};
    `;
  }
}

const resetColor = document.getElementById("reset_color");
if(resetColor != null) {
  resetColor.addEventListener("click", () => {
    Swal.fire({
      title: 'Anda yakin ingin reset ke warna tema default?',
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#DC3545',
      confirmButtonText: 'Reset',
      cancelButtonColor: '#6C757D',
      cancelButtonText: "Tutup"
    }).then((result) => {
      if (result.isConfirmed) {
        iLoader.start();
        setTimeout(() => {
          Swal.fire({
            title: 'Reset warna tema berhasil!',
            icon: 'success',
            confirmButtonColor: '#6C757D',
            confirmButtonText: 'Tutup',
          })
          resetThemes();
          iLoader.stop();
        }, 1000);
      }
    })
  });
}


// CUSTOM LAYOUT
window.onload = () => {
	let body = document.querySelector("body");

	if (body.classList.contains("custom-layout-2")) {
		setTimeout(() => {
			let collapse = document.querySelector(".collapse.in");
			if (collapse != null) {
				collapse.classList.remove("in");
			}
		}, 10);

		var backToTop = document.querySelector(".scroll-top");
		backToTop.addEventListener("click", () => {
			document.body.scrollTop = 0; // For Safari
			document.documentElement.scrollTop = 0; // For Chrome, Firefox, IE and Opera
		})

		this.onscroll = () => {
			if (document.body.scrollTop > 200 || document.documentElement.scrollTop > 200) {
				backToTop.style.display = "block";
			} else {
				backToTop.style.display = "none";
			}
		}

		$(function () {
			function e() {
				var e = (0 < window.innerHeight ? window.innerHeight : this.screen.height) - 1;
				(e -= 108) < 1 && (e = 1), 108 < e && $(".page-wrapper").css("min-height", e + "px")
			}
			$(window).ready(e);
			$(window).on("resize", e);
		})
	}
}


// ENABLE BOOTSTRAP TOOLTIPS
const tooltipList = [...document.querySelectorAll('[data-bs-toggle="tooltip"]')].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl));


// ENABLE SELECT2
$(document).ready(function() {
  const select2Arr = [...document.getElementsByClassName("select2")];
  select2Arr.forEach(el => {
    const modalParent = el.closest(".modal");
    if(modalParent != null) {
      $(el).select2({
        dropdownParent: $(`#${modalParent.id}`),
      });
    } else {
      $(el).select2();
    }
  })
});


// WELCOME TOAST
const showWelcomeToast = (name) => {
	const welcomeToast = sessionStorage.getItem(`${appPrefix}Toast`);
	if(welcomeToast != null) {
		toastr.info(`Selamat datang, ${name}`);
		sessionStorage.removeItem(`${appPrefix}Toast`);
	}
}


// SWEETALERT DELETE CONFIRMATION
const confirmDelete = () => {
	const atrDel = [...document.getElementsByClassName("atr_del")];
	if (atrDel != null) {
		atrDel.forEach(el => {
			el.addEventListener("click", () => {
				const deleteURL = el.getAttribute("data-item-delete");
				const confirmMessage = el.getAttribute("data-confirm-message");
				Swal.fire({
					title: confirmMessage,
					icon: 'warning',
					showCancelButton: true,
					confirmButtonColor: '#DC3545',
					confirmButtonText: 'Hapus',
					cancelButtonColor: '#6C757D',
					cancelButtonText: "Tutup"
				}).then((result) => {
					if (result.isConfirmed) {
						window.location.href = `${BASE_URL}${deleteURL}`;
					}
				})
			})
		})
	}
}


// SWEETALERT ACTIVE CONFIRMATION
const confirmActive = () => {
	const atrActive = [...document.getElementsByClassName("atr_active")];
	if (atrActive != null) {
		atrActive.forEach(el => {
			el.addEventListener("click", () => {
				const activeURL = el.getAttribute("data-item-active");
				const confirmMessage = el.getAttribute("data-confirm-message");
				Swal.fire({
					title: confirmMessage,
					icon: 'warning',
					showCancelButton: true,
					confirmButtonColor: '#198754',
					confirmButtonText: 'Ya',
					cancelButtonColor: '#6C757D',
					cancelButtonText: "Tutup"
				}).then((result) => {
					if (result.isConfirmed) {
						window.location.href = `${BASE_URL}${activeURL}`;
					}
				})
			})
		})
	}
}


// ECHARTS INIT
const echarts_init = (elemId, chartOptions) => {
  const chartDom = document.getElementById(elemId);
  const myChart = echarts.init(chartDom);

  chartOptions && myChart.setOption(chartOptions);
}


// CKEDITOR INIT
const ckeditorInit = [...document.getElementsByClassName("ckeditor4")];
ckeditorInit.forEach(el => {
  const placeholder = el.getAttribute("data-placeholder");
  CKEDITOR.replace(el, {
    customConfig: "../../js/ckeditor.config.js",
    editorplaceholder: `${placeholder}`
  });
})


// DATEPICKER INIT (Kunci maksimal hari ini untuk seluruh tanggal transaksi, kecuali field proyeksi/jatuh tempo)
function isFutureDateAllowed($el) {
  if ($el.hasClass('allow-future')) return true;
  var id = ($el.attr('id') || '').toLowerCase();
  var name = ($el.attr('name') || '').toLowerCase();
  var futurePatterns = ['deadline', 'exp', 'tempo', 'expected', 'filter_end', 'filter_to', 'date_to'];
  for (var i = 0; i < futurePatterns.length; i++) {
    if (id.indexOf(futurePatterns[i]) !== -1 || name.indexOf(futurePatterns[i]) !== -1) {
      return true;
    }
  }
  return false;
}

const datepickerInit = document.getElementsByClassName("datepicker");
if(datepickerInit != null) {
  $('.datepicker').each(function() {
    var $this = $(this);
    var opts = {
      format: "dd MM yyyy",
      language: "id",
      autoclose: true,
      clearBtn: true         // Menambahkan tombol "Clear"
    };
    if (!isFutureDateAllowed($this)) {
      opts.endDate = new Date();
    }
    $this.datepicker(opts);
  });
}

const datepickerxInit = document.getElementsByClassName("datepickerx");
if(datepickerxInit != null) {
  $('.datepickerx').each(function() {
    var $this = $(this);
    var opts = {
      format: "dd-mm-yyyy",
      language: "id",
      autoclose: true
    };
    if (!isFutureDateAllowed($this)) {
      opts.endDate = new Date();
    }
    $this.datepicker(opts);
  });
}


// PERFECT SCROLLBAR INIT
const psInit = document.getElementsByClassName("perfect-scroll");
if(psInit != null) {
	$(".perfect-scroll").perfectScrollbar();
}


// DATATABLES INIT
const dataTablesInit = [...document.getElementsByClassName("datatable")];
if(dataTablesInit != null) {
  dataTablesInit.forEach(dt => {
    const dtPageLength = dt.getAttribute("dt-page-length");
    const dtOrderInt = dt.getAttribute("dt-order-int");
    const dtOrderSort = dt.getAttribute("dt-order-sort");
    $(dt).DataTable({
      responsive: false,
      pageLength: dtPageLength ? dtPageLength : 10,
      order: dtOrderInt && dtOrderSort ? [[dtOrderInt, dtOrderSort]] : [],
      language: {
        lengthMenu: "Menampilkan _MENU_ entri per-halaman",
        zeroRecords: "Data tidak ditemukan",
        info: "Menampilkan _START_ - _END_ dari _TOTAL_ entri",
        infoEmpty: "Tidak ada data",
        infoFiltered: "(difilter dari total _MAX_ entri)",
        paginate: {
          next: "Selanjutnya",
          previous: "Sebelumnya"
        },
        search: "Pencarian",
      },
    });
  })
}


// FORMAT & UNFORMAT IDR CURRENCY
const formatIDR = (param) => (
  new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR' }).format(param)
)

const unformatIDR = (param) => {
  const arr = [...param];
  arr.splice(0,3);
  const arrRes = arr.join("");

  const decArr = arrRes.split(".").join("").split(",").join(".");

  return parseFloat(decArr);
}

const formIDR = [...document.getElementsByClassName("form-idr")];
formIDR.forEach(el => {
  if(el.value != "") {
    let val = Number(el.value);
    el.value = formatIDR(val);
  }

  el.addEventListener("change", () => {
    val = Number(el.value);
    if(!isNaN(val)) {
      val = Number(el.value);
      el.value = formatIDR(val);
    } else {
      el.value = "";
    }
    el.blur();
  })
  el.addEventListener("focus", () => {
    el.value = "";
  })
})


// BOOTSTRAP FORM VALIDATION
const btnLogin = document.querySelector('#loginform #btnSubmit');

const btnLoginMod = {
  enableLogin() {
    btnLogin.removeAttribute('disabled')
    btnLogin.innerHTML = 'Login'
  },
  disableLogin(msg = 'Isi captcha untuk Login') {
    btnLogin.setAttribute('disabled', true)
    btnLogin.innerHTML = msg
  },
  startLoading() {
    btnLogin.innerHTML = '<i class="fa fa-spinner fa-spin"></i>'
  },
}

const formsValidate = [...document.getElementsByClassName("needs-validation")];
if(formsValidate != null) {
  formsValidate.forEach(form => {
    form.addEventListener("submit", event => {
      if(!form.checkValidity()) {
        event.preventDefault();
        event.stopPropagation();
      } else {
        if (form.getAttribute('id') === 'loginform') {
          btnLoginMod.startLoading();
          event.preventDefault();
          event.stopPropagation();
          (async () => {
            const req = await fetch(`${BASE_URL}validate_captcha`, { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({numcha:document.querySelector('#numcha-input').value}) }).then(res => res.status)

            if (req === 200) {
              form.submit();
              sessionStorage.setItem(`${appPrefix}Toast`, true);
            } else {
              Swal.fire({
                title: 'Captcha tidak valid.',
                icon: 'error',
                confirmButtonColor: '#6C757D',
                confirmButtonText: 'Tutup',
              })
              btnLoginMod.enableLogin();
            }
          })()
        }

        const fieldIDR = [...form.getElementsByClassName("form-idr")];
        fieldIDR.forEach(field => {
          field.value = unformatIDR(field.value);
        })
      }

      form.classList.add("was-validated")
    })
  })
}


// CUSTOM INPUT FILE DRAG & DROP
const inputFiles = [...document.querySelectorAll(".file-drag-drop")];
if(inputFiles != null) {
  inputFiles.forEach(inputFile => {
    const customSpan = document.createElement("span");
    customSpan.classList.add("drag-drop-label");
    customSpan.setAttribute("data-label", "📂 Drag & drop atau pilih file");

    inputFile.parentElement.style.position = "relative";
    inputFile.parentNode.insertBefore(customSpan, inputFile.nextSibling);

    inputFile.addEventListener("change", () => {
      const collection = [];

      const filesArr = [...inputFile.files];
      filesArr.forEach(file => {
        collection.push(file.name);
      })

      const labelSpan = inputFile.nextSibling;
      const fileName = collection.join(", ");

      labelSpan.setAttribute("data-label", `📂 ${fileName}`);
      labelSpan.classList.add("text-dark");
    })
  })
}


// FIX BUG BOOTSTRAP ACCORDION ON TABULATOR
/**
 * init this function with dataProcessed method
 * on respectively Tabulator config
 */
const fixAccordion = () => {
  const accordFlushes = [...document.getElementsByClassName("accordion-flush")];
  if(accordFlushes != null) {
    accordFlushes.forEach(accord => {
      accord.addEventListener("click", () => {
        const tabCell = accord.closest(".tabulator-cell");
        tabCell.style.transition = "all .3s";
        const cellHeight = tabCell.offsetHeight;
        const accordHeight1 = accord.offsetHeight;

        if(accord.classList.contains("open")) {
          setTimeout(() => {
            const accordHeight2 = accord.offsetHeight;
            tabCell.style.height = `${cellHeight + accordHeight2 - accordHeight1}px`;
          }, 300);
          accord.classList.remove("open");
        } else {
          setTimeout(() => {
            const accordHeight2 = accord.offsetHeight;
            tabCell.style.height = `${cellHeight + accordHeight2 - accordHeight1}px`;
          }, 300);
          accord.classList.add("open");
        }
      })
    })
  }
}


// PASSWORD VISIBILITY TOGGLE
const togglePasswords = [...document.querySelectorAll('.toggle-password')]

if (togglePasswords) {
  togglePasswords.forEach(el => {
    el.addEventListener('click', function() {
      const inputPassword = this.previousElementSibling
  
      if (inputPassword.getAttribute('type') === 'text') {
        inputPassword.setAttribute('type', 'password')
        this.classList.remove('fa-eye-slash')
        this.classList.add('fa-eye')
      } else {
        inputPassword.setAttribute('type', 'text')
        this.classList.remove('fa-eye')
        this.classList.add('fa-eye-slash')
      }
    })
  })
}


// NUMBER CAPTCHA (NUMCHA) GENERATOR
const numchaRefresh = document.querySelector('.numcha-refresh');

const getRndInteger = (min, max) => {
  return Math.floor(Math.random() * (max - min)) + min;
}

const numchaGenerate = async () => {
  const numchaContainer = document.querySelector('.numcha-container');
  const numchaNum1 = document.querySelector('#numcha-num-1');
  const numchaNum2 = document.querySelector('#numcha-num-2');
  const numchaNum3 = document.querySelector('#numcha-num-3');
  const numchaNum4 = document.querySelector('#numcha-num-4');
  const numchaInput = document.querySelector('#numcha-input');

  numchaRefresh.setAttribute('disabled',true);
  setTimeout(() => {
    numchaRefresh.removeAttribute('disabled');
  }, 750);

  btnLoginMod.disableLogin();
  numchaInput.value = '';

  const reqCaptcha = await fetch(`${BASE_URL}req_captcha`, { method: 'POST' }).then(res => res.json()).then(data => data.toString());

  numchaNum1.innerHTML = reqCaptcha[0];
  numchaNum2.innerHTML = reqCaptcha[1];
  numchaNum3.innerHTML = reqCaptcha[2];
  numchaNum4.innerHTML = reqCaptcha[3];

  numchaNum1.style.transform = `rotate(${getRndInteger(-16, 24)}deg)`;
  numchaNum2.style.transform = `rotate(${getRndInteger(-16, 24)}deg)`;
  numchaNum3.style.transform = `rotate(${getRndInteger(-16, 24)}deg)`;
  numchaNum4.style.transform = `rotate(${getRndInteger(-16, 24)}deg)`;

  numchaContainer.style.backgroundPosition = `${Math.floor(Math.random() * 541)}px ${Math.floor(Math.random() * 361)}px`;

  numchaInput.addEventListener('keyup', function() {
    if (this.value === reqCaptcha)
      btnLoginMod.enableLogin()
    else if (this.value !== '')
      btnLoginMod.disableLogin('Kode captcha tidak sesuai')
    else
      btnLoginMod.disableLogin()
  })
}

if (numchaRefresh !== null) {
  numchaRefresh.addEventListener('click', () => {
    numchaGenerate()
  })
}