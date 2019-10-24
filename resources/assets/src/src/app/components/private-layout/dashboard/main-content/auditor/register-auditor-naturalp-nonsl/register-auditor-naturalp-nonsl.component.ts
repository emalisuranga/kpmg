import { ToastrService } from 'ngx-toastr';
import { NgxSpinnerService } from 'ngx-spinner';
import { Component, OnInit, HostListener } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { HttpHeaders } from '@angular/common/http';
import { Router, ActivatedRoute } from '@angular/router';
import { DomSanitizer } from '@angular/platform-browser';
import { AuditorDataService } from '../auditor-data.service';
import { Item, IBuy } from './../../../../../../http/models/payment';
import { DataService } from '../../../../../../storage/data.service';
import { IAuditorData } from '../../../../../../http/models/auditor.model';
import { HelperService } from '../../../../../../http/shared/helper.service';
import { GeneralService } from '../../../../../../http/services/general.service';
import { AuditorService } from '../../../../../../http/services/auditor.service';
import { PaymentService } from '../../../../../../http/services/payment.service';
import { CalculationService } from './../../../../../../http/shared/calculation.service';
import { APIConnection } from '../../../../../../http/services/connections/APIConnection';
import { environment } from '../../../../../../../environments/environment';
declare var google: any;

@Component({
  selector: 'app-register-auditor-naturalp-nonsl',
  templateUrl: './register-auditor-naturalp-nonsl.component.html',
  styleUrls: ['./register-auditor-naturalp-nonsl.component.scss']
})
export class RegisterAuditorNaturalpNonslComponent implements OnInit {


  url: APIConnection = new APIConnection();
  paymentGateway: string = environment.paymentGateway;

  auditorDetails: IAuditorData = {
    registeredUser: false, nic: '', passport: '', loggedInUser: '', id: 0, title: '', sinFullName: '', tamFullName: '', sinAd: '', tamAd: '',
    firstname: '', lastname: '', residentialLocalAddress1: '', residentialLocalAddress2: '', residentialProvince: null, residentialDistrict: null,
    residentialCity: null, rgnDivision: null, residentialPostCode: '', businessName: '', businessLocalAddress1: '', businessLocalAddress2: '',
    businessProvince: '', businessDistrict: '', businessCity: '', gnDivision: '', businessPostCode: '', birthDay: '',
    pQualification: '', nationality: '', race: '', whereDomiciled: '', dateTakeResidenceInSrilanka: '',
    dateConResidenceInSrilanka: '', ownedProperty: '', otherFacts: '',
    isUnsoundMind: '', isInsolventOrBankrupt: '', reason1: '', isCompetentCourt: '', reason2: '', otherDetails: '', subClauseQualified: '', isExistAud: '', certificateNo: '',
  };

  passport: string;
  loggedinUserEmail: string;
  audId: number;
  enableStep1Submission = false;
  enableStep2Submission = false;
  enableGoToPay = false;
  blockBackToForm = false;
  blockPayment = false;

  application = [];
  pCertificateUploadList = [];
  description: string;
  cipher_message: string;

  residentialProvince: string;
  residentialDistrict: string;
  residentialCity: string;
  rgnDivision: string;

  businessProvince: string;
  businessDistrict: string;
  businessCity: string;
  bgnDivision: string;

  sinFullName = null;
  tamFullName = null;
  tamilelements;
  sinElements;

  sinAd = null;
  tamAd = null;
  adtamilelements;
  adsinElements;

  stepOn = 0;
  processStatus: string;
  progress = {
    stepArr: [
      { label: 'Personal Details', icon: 'fa fa-list-ol', status: 'active' },
      { label: 'Qualifications', icon: 'fa fa-users', status: '' },
      { label: 'Download Documents', icon: 'fa fa-download', status: '' },
      { label: 'Upload Documents', icon: 'fa fa-upload', status: '' },
      { label: 'Payments', icon: 'fa fa-money-bill-alt', status: '' },
    ],
    progressPercentage: '10%'
  };

  constructor(
    public data: DataService,
    private helper: HelperService,
    private auditorService: AuditorService,
    private sanitizer: DomSanitizer,
    private route: ActivatedRoute,
    private router: Router,
    private spinner: NgxSpinnerService,
    private httpClient: HttpClient,
    private general: GeneralService,
    private AudData: AuditorDataService,
    private crToken: PaymentService,
    private snotifyService: ToastrService,
    public calculation: CalculationService,
  ) {
    this.audId = this.AudData.getAudId; // for continue upload process after canceled...

    if (!(this.audId === undefined)) {
      this.loadUploadedFile(this.audId);
      this.changeProgressStatuses(2);
      this.AudData.audId = undefined;
    }
    this.passport = route.snapshot.paramMap.get('passport');
    this.loadAuditorData(this.passport);
    this.loggedinUserEmail = localStorage.getItem('currentUser');
    this.loggedinUserEmail = this.loggedinUserEmail.replace(/^"(.*)"$/, '$1');

  }

  ngOnInit() {
    document.getElementById('div1').style.display = 'none';
    document.getElementById('div2').style.display = 'none';
  }

  /*....below show () functions for the radio buttons validations....*/
  show1() {
    document.getElementById('div1').style.display = 'none';
    this.auditorDetails['reason1'] = '';
  }
  show2() {
    document.getElementById('div1').style.display = 'block';
  }
  show3() {
    document.getElementById('div2').style.display = 'none';
    this.auditorDetails['reason2'] = '';
  }
  show4() {
    document.getElementById('div2').style.display = 'block';
  }
  sanitize(url: string) {
    return this.sanitizer.bypassSecurityTrustUrl(url);
  }

  changeProgressStatuses(newStatus = 1) {
    this.stepOn = newStatus;
    this.progress.progressPercentage = (this.stepOn >= 4) ? (10 * 2 + this.stepOn * 20) + '%' : (10 + this.stepOn * 20) + '%';
    for (let i = 0; i < this.progress['stepArr'].length; i++) {
      if (this.stepOn > i) {
        this.progress['stepArr'][i]['status'] = 'activated';
      } else if (this.stepOn === i) {
        this.progress['stepArr'][i]['status'] = 'active';
      } else {
        this.progress['stepArr'][i]['status'] = '';
      }
    }
    return this.progress;
  }

  loadAuditorData(passport) {
    const data = {
      passport: passport,
    };
    this.auditorService.auditorDataNonSL(data)
      .subscribe(
        req => {
          if (req['status']) {
            this.auditorDetails.title = req['data']['auditor']['title'];
            this.auditorDetails.firstname = req['data']['auditor']['first_name'];
            this.auditorDetails.lastname = req['data']['auditor']['last_name'];
            this.auditorDetails.birthDay = req['data']['auditor']['dob'];
            this.auditorDetails.registeredUser = req['user'];
          }
          this.auditorValidationStep1();
        },
        error => {
          console.log(error);
        }
      );
  }
  auditorDataSubmit() {
    if (this.auditorDetails['isInsolventOrBankrupt'] === 'no') {
      this.auditorDetails['reason1'] = '';
    }
    if (this.auditorDetails['isCompetentCourt'] === 'no') {
      this.auditorDetails['reason2'] = 'no';
    }
    const data = {
      nic: '',
      sinFullName: this.sinFullName,
      tamFullName: this.tamFullName,
      sinAd: this.sinAd,
      tamAd: this.tamAd,
      passport: this.passport,
      isExistAud: this.auditorDetails['isExistAud'],
      certificateNo: this.auditorDetails['certificateNo'],
      loggedInUser: this.loggedinUserEmail,
      registeredUser: this.auditorDetails['registeredUser'],
      id: this.auditorDetails['id'],
      title: this.auditorDetails['title'],
      firstname: this.auditorDetails['firstname'],
      lastname: this.auditorDetails['lastname'],
      residentialLocalAddress1: this.auditorDetails['residentialLocalAddress1'],
      residentialLocalAddress2: this.auditorDetails['residentialLocalAddress2'],
      residentialPostCode: this.auditorDetails['residentialPostCode'],
      residentialProvince: this.auditorDetails.residentialProvince.description_en === undefined ? this.residentialProvince : this.auditorDetails.residentialProvince.description_en,
      residentialDistrict: this.auditorDetails.residentialDistrict.description_en === undefined ? this.residentialDistrict : this.auditorDetails.residentialDistrict.description_en,
      residentialCity: this.auditorDetails.residentialCity.description_en === undefined ? this.residentialCity : this.auditorDetails.residentialCity.description_en,
      rgnDivision: this.auditorDetails.rgnDivision.description_en === undefined ? this.rgnDivision : this.auditorDetails.rgnDivision.description_en,
      businessName: this.auditorDetails['businessName'],
      businessLocalAddress1: this.auditorDetails['businessLocalAddress1'],
      businessLocalAddress2: this.auditorDetails['businessLocalAddress2'],
      // businessProvince: this.auditorDetails.businessProvince.description_en,
      // businessDistrict: this.auditorDetails.businessDistrict.description_en,
      // businessCity: this.auditorDetails.businessCity.description_en,
      // gnDivision: this.auditorDetails.gnDivision.description_en,
      businessProvince: this.auditorDetails.businessProvince.description_en === undefined ? this.businessProvince : this.auditorDetails.businessProvince.description_en,
      businessDistrict: this.auditorDetails.businessDistrict.description_en === undefined ? this.businessDistrict : this.auditorDetails.businessDistrict.description_en,
      businessCity: this.auditorDetails.businessCity.description_en === undefined ? this.businessCity : this.auditorDetails.businessCity.description_en,
      gnDivision: this.auditorDetails.gnDivision.description_en === undefined ? this.bgnDivision : this.auditorDetails.gnDivision.description_en,
      businessPostCode: this.auditorDetails['businessPostCode'],
      birthDay: this.auditorDetails['birthDay'],
      pQualification: this.auditorDetails['pQualification'],
      nationality: this.auditorDetails['nationality'],
      race: this.auditorDetails['race'],
      whereDomiciled: this.auditorDetails['whereDomiciled'],
      dateTakeResidenceInSrilanka: this.auditorDetails['dateTakeResidenceInSrilanka'],
      dateConResidenceInSrilanka: this.auditorDetails['dateConResidenceInSrilanka'],
      ownedProperty: this.auditorDetails['ownedProperty'],
      otherFacts: this.auditorDetails['otherFacts'],
      isUnsoundMind: this.auditorDetails['isUnsoundMind'],
      isInsolventOrBankrupt: this.auditorDetails['isInsolventOrBankrupt'],
      reason1: this.auditorDetails['reason1'],
      isCompetentCourt: this.auditorDetails['isCompetentCourt'],
      reason2: this.auditorDetails['reason2'],
      otherDetails: this.auditorDetails['otherDetails'],
      subClauseQualified: this.auditorDetails['subClauseQualified'],
    };
    this.auditorService.auditorDataSubmit(data)
      .subscribe(
        req => {
          if (req['status']) {
            this.blockBackToForm = false;
            this.audId = req['audId'];
            this.changeProgressStatuses(2);
          }
        },
        error => {
          console.log(error);
        }
      );
  }

  loadsinhala() {
    const sinhalOptions = {
      sourceLanguage: google.elements.transliteration.LanguageCode.ENGLISH,
      destinationLanguage: [google.elements.transliteration.LanguageCode.SINHALESE],
      shortcutKey: 'ctrl+s',
      transliterationEnabled: true
    };
    const TrSinhalaControl = new google.elements.transliteration.TransliterationControl(sinhalOptions);
    TrSinhalaControl.makeTransliteratable(this.sinElements);
  }

  loadTamil() {
    const tamilOptions = {
      sourceLanguage: google.elements.transliteration.LanguageCode.ENGLISH,
      destinationLanguage: [google.elements.transliteration.LanguageCode.TAMIL],
      shortcutKey: 'ctrl+s',
      transliterationEnabled: true
    };
    const TrtamilControl = new google.elements.transliteration.TransliterationControl(tamilOptions);
    TrtamilControl.makeTransliteratable(this.tamilelements);
  }

  @HostListener('keydown', ['$event']) onKeyDown(e) {
    if (e.keyCode === 32 || e.keyCode === 13 || e.keyCode === 46 || e.keyCode === 8) {
      setTimeout(() => {
        this.sinFullName = this.sinElements[0].value;
        this.tamFullName = this.tamilelements[0].value;
      },
        1000);
    }
  }
  // for download the generated pdf...
  clickDownload() {
    this.auditorGeneratePDF(this.audId);
  }
  auditorGeneratePDF(audId) {
    this.spinner.show();
    this.auditorService.auditorPDF(audId)
      .subscribe(
        response => {
          this.spinner.hide();
          this.helper.download(response);
        },
        error => {
          console.log(error);
          this.spinner.hide();
        }
      );
  }
  // for uplaod auditor pdf files...
  fileUpload(event, description, docType) {

    let fileList: FileList = event.target.files;
    if (fileList.length > 0) {
      let file: File = fileList[0];
      let fileSize = fileList[0].size;
      let filetype = fileList[0].type;
      if (fileSize > 1024 * 1024 * 4) { // 4mb restriction
        alert('File size should be less than 4 MB');
        return false;
      }
      if (!filetype.match('application/pdf')) {
        alert('Please upload pdf files only');
        return false;
      }

      let formData: FormData = new FormData();

      formData.append('uploadFile', file, file.name);
      formData.append('docType', docType);
      formData.append('audId', this.audId.toString());
      formData.append('description', description);
      formData.append('filename', file.name);

      let headers = new HttpHeaders();
      headers.append('Content-Type', 'multipart/form-data');
      headers.append('Accept', 'application/json');

      let uploadurl = this.url.getAuditorFileUploadUrl();
      this.spinner.show();

      this.httpClient.post(uploadurl, formData, { headers: headers })
        .subscribe(
          (data: any) => {
            const datas = {
              id: data['docid'],
              name: data['name'],
              token: data['token'],
              pdfname: data['pdfname'],
            };
            if (docType === 'applicationUpload') {
              this.application.push(datas);
              this.gotoPay();
            } else if (docType === 'pCertificateUpload') {
              this.pCertificateUploadList.push(datas);
            }
            this.spinner.hide();
            this.description = '';
          },
          error => {
            console.log(error);
            this.spinner.hide();
          }
        );
    }
  }
  // for delete the uploaded pdf from the database...
  fileDelete(docId, docType, index) {
    const data = {
      documentId: docId,
    };
    this.spinner.show();
    this.auditorService.auditorDeleteUploadedPdf(data)
      .subscribe(
        rq => {
          this.spinner.hide();
          if (index > -1) {
            if (docType === 'applicationUpload') {
              this.application.splice(index, 1);
              this.gotoPay();
            } else if (docType === 'pCertificateUpload') {
              this.pCertificateUploadList.splice(index, 1);
            }
          }
        },
        error => {
          this.spinner.hide();
          console.log(error);
        }
      );
  }
  // for load uplaoded auditor all pdf files...
  loadUploadedFile(audId) {
    const data = {
      audId: audId,
      type: 'individual',
    };
    this.auditorService.auditorFiles(data)
      .subscribe(
        req => {
          if (req['status']) {
            if (req['data']['file']) {
              for (let i in req['data']['file']) {
                this.application = [];
                this.pCertificateUploadList = [];
                const data1 = {
                  id: req['data']['file'][i]['id'],
                  key: req['data']['file'][i]['dockey'],
                  token: req['data']['file'][i]['file_token'],
                  pdfname: req['data']['file'][i]['name'],
                };
                if (req['data']['file'][i]['dockey'] === 'AUDITOR_APPLICATION') {
                  this.application.push(data1);
                  this.gotoPay();
                } else if (req['data']['file'][i]['dockey'] === 'AUDITOR_PROFESSIONAL_QUALIFICATION') {
                  this.pCertificateUploadList.push(data1);
                }
              }
            }
          }
        }
      );
  }
  // for view the uploaded pdf...
  ngOnDownload(token: string): void {
    this.spinner.show();
    this.general.getDocumenttoServer(token, 'CAT_AUDITOR_DOCUMENT')
      .subscribe(
        response => {
          this.helper.download(response);
          this.spinner.hide();
        },
        error => {
          this.spinner.hide();
        }
      );
  }
  // for the payment process...
  auditorPay() {
    const data = {
      audId: this.audId,
      audType: 'individual',
    };
    this.auditorService.auditorPay(data)
      .subscribe(
        req => {
          if (req['status']) {
            alert('Payment Successful');
            this.router.navigate(['dashboard/selectregisterauditor']);
          }
        },
        error => {
          console.log(error);
        }
      );
  }
  getCipherToken() {
    if (!this.audId) { return this.router.navigate(['dashboard/selectregisterauditor']); }
    const item: Array<Item> = [{
      fee_type: 'PAYMENT_INDIVIUAL_AS_AUDITORS',
      description: 'For register of a Auditor (Register Request)',
      quantity: 1,
    }];
    const buy: IBuy = {
      module_type: 'MODULE_AUDITOR',
      module_id: this.audId.toString(),
      description: 'Auditor Registration Non Srilankan',
      item: item,
      extraPay: null
    };
    this.crToken.getCrToken(buy).subscribe(
      req => {
        this.cipher_message = req.token;
        this.blockPayment = true;
      },
      error => { this.snotifyService.error(error, 'error'); }
    );
  }

  changeSubClause() {
    if (this.auditorDetails.subClauseQualified === '') {
      this.auditorDetails.pQualification = '';
    } else if (this.auditorDetails.subClauseQualified === '5(1)a') {
      this.auditorDetails.pQualification = 'Possess Certificate to Practice issued by the council of the Institute of chartered accountants of Ceylon';
    } else if (this.auditorDetails.subClauseQualified === '5(1)b') {
      this.auditorDetails.pQualification = 'Has been an officer in the department of Inland revenue not below the rank of assessor';
    } else if (this.auditorDetails.subClauseQualified === '5(1)c') {
      this.auditorDetails.pQualification = 'Posses Diploma in accountancy granted by the Ceylon Technical college or any institute or society recommend by the board';
    } else if (this.auditorDetails.subClauseQualified === '5(1)d') {
      this.auditorDetails.pQualification = 'Has been a member of the Ceylon Audit service not below the rank of superintendent of audit. Class II, Being a person who has been appointed to that service on the results of competitive examination';
    } else if (this.auditorDetails.subClauseQualified === '5(1)e') {
      this.auditorDetails.pQualification = 'Has been a member of the Ceylon government Accountants service not below class III of that service being a person appointed to that service on the results of competitive examination';
    } else if (this.auditorDetails.subClauseQualified === '5(1)f') {
      this.auditorDetails.pQualification = 'A member of any other institute or society of accountants or secretaries approved by the board';
    }
    this.auditorValidationStep2();
  }

  isUnsoundMindPopUp() {
    if (this.auditorDetails.isUnsoundMind === 'yes') {
      alert('Warning! You cannot proceed further');
    }
  }

  auditorValidationStep1() {
    if (
      this.auditorDetails.title &&
      this.auditorDetails.firstname &&
      this.auditorDetails.lastname &&
      this.auditorDetails.birthDay &&
      this.auditorDetails.nationality &&
      this.auditorDetails.race &&
      this.auditorDetails.whereDomiciled &&
      this.auditorDetails.dateTakeResidenceInSrilanka &&
      this.auditorDetails.dateConResidenceInSrilanka &&
      this.auditorDetails.ownedProperty &&
      this.auditorDetails.otherFacts
    ) {
      this.enableStep1Submission = true;
      if (this.auditorDetails.businessName) {
        if (this.auditorDetails.businessLocalAddress1 &&
          this.auditorDetails.businessLocalAddress2 &&
          this.auditorDetails.businessProvince &&
          this.auditorDetails.businessDistrict &&
          this.auditorDetails.businessCity &&
          this.auditorDetails.gnDivision &&
          this.auditorDetails.businessPostCode

        ) {
          this.enableStep1Submission = true;
        } else {
          this.enableStep1Submission = false;
        }
      } else {
        if (this.auditorDetails.businessLocalAddress1 &&
          this.auditorDetails.businessLocalAddress2 &&
          this.auditorDetails.businessProvince &&
          this.auditorDetails.businessDistrict &&
          this.auditorDetails.businessCity &&
          this.auditorDetails.gnDivision &&
          this.auditorDetails.businessPostCode

        ) {
          this.enableStep1Submission = false;
        } else {
          this.enableStep1Submission = true;
        }
      }
    } else {
      this.enableStep1Submission = false;
    }
  }
  auditorValidationStep2() {
    if (
      this.auditorDetails.pQualification &&
      this.auditorDetails.subClauseQualified &&
      (this.auditorDetails.isUnsoundMind === 'no') &&
      (this.auditorDetails.isInsolventOrBankrupt === 'yes' || this.auditorDetails.isInsolventOrBankrupt === 'no') &&
      (this.auditorDetails.isCompetentCourt === 'yes' || this.auditorDetails.isCompetentCourt === 'no')
    ) {
      this.enableStep2Submission = true;
      if (this.auditorDetails.isInsolventOrBankrupt === 'yes') {
        if (this.auditorDetails.reason1) {
          this.enableStep2Submission = true;
          if (this.auditorDetails.isCompetentCourt === 'yes') {
            if (this.auditorDetails.reason2 === 'pardoned' || this.auditorDetails.reason2 === 'appeal') {
              this.enableStep2Submission = true;
            } else {
              this.enableStep2Submission = false;
            }
          } else if (this.auditorDetails.isCompetentCourt === 'no') {
            if (this.auditorDetails.reason2 === 'pardoned' || this.auditorDetails.reason2 === 'appeal') {
              this.enableStep2Submission = false;
            } else {
              this.enableStep2Submission = true;
            }
          }
        } else {
          this.enableStep2Submission = false;
        }
      } else if (this.auditorDetails.isInsolventOrBankrupt === 'no') {
        if (this.auditorDetails.reason1) {
          this.enableStep2Submission = false;
        } else {
          this.enableStep2Submission = true;

          if (this.auditorDetails.isCompetentCourt === 'yes') {
            if (this.auditorDetails.reason2 === 'pardoned' || this.auditorDetails.reason2 === 'appeal') {
              this.enableStep2Submission = true;
            } else {
              this.enableStep2Submission = false;
            }
          } else if (this.auditorDetails.isCompetentCourt === 'no') {
            if (this.auditorDetails.reason2 === 'pardoned' || this.auditorDetails.reason2 === 'appeal') {
              this.enableStep2Submission = false;
            } else {
              this.enableStep2Submission = true;
            }
          }
        }
      }
    } else {
      this.enableStep2Submission = false;
    }
  }
  gotoPay() {
    if (typeof this.application !== 'undefined' && this.application != null && this.application.length != null && this.application.length > 0) {
      this.enableGoToPay = true;
    } else {
      this.enableGoToPay = false;
    }

  }
  // for confirm to going document download step...
  areYouSureYes() {
    this.blockBackToForm = true;
  }
  areYouSureNo() {
    this.blockBackToForm = false;
  }
  // for confirm to complete payment step...
  areYouSurePayYes() {
    this.getCipherToken();
    this.blockPayment = true;
  }
  areYouSurePayNo() {
    this.blockPayment = false;
  }

  dateValidate(type) {
    if (type === 'birthday') {
      const date = this.auditorDetails.birthDay;
      let sendDate: Date = new Date(Date.parse(date.replace(/-/g, ' ')));
      let today = new Date();
      today.setHours(0, 0, 0, 0);
      const age = this.getAge(date);
      if (sendDate > today) {
        alert('The date of birth can\'t be in the future. Please pick another date.');
        this.auditorDetails.birthDay = '';
      } else if (21 > age) {
        alert('Unless you have attained the age of 21 years, you can\'t register as an auditor.');
        this.auditorDetails.birthDay = '';
      }
      return false;
    } else if (type === 'dtr') {
      const date = this.auditorDetails.dateTakeResidenceInSrilanka;
      let sendDate: Date = new Date(Date.parse(date.replace(/-/g, ' ')));
      let today = new Date();
      today.setHours(0, 0, 0, 0);
      if (sendDate > today) {
        alert('The date can\'t be in the future. Please pick another date.');
        this.auditorDetails.dateTakeResidenceInSrilanka = '';
      }
      return false;
    } else if (type === 'dcr') {
      const date = this.auditorDetails.dateConResidenceInSrilanka;
      let sendDate: Date = new Date(Date.parse(date.replace(/-/g, ' ')));
      let today = new Date();
      today.setHours(0, 0, 0, 0);
      if (sendDate > today) {
        alert('The date can\'t be in the future. Please pick another date.');
        this.auditorDetails.dateConResidenceInSrilanka = '';
      }
      return false;
    }
  }

  getAge(DOB) {
    var today = new Date();
    var birthDate = new Date(DOB);
    var age = today.getFullYear() - birthDate.getFullYear();
    var m = today.getMonth() - birthDate.getMonth();
    if (m < 0 || (m === 0 && today.getDate() < birthDate.getDate())) {
      age = age - 1;
    }
    return age;
  }

}
