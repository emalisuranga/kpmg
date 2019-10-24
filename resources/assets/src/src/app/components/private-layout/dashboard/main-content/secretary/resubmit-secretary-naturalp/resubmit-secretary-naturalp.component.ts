import { ToastrService } from 'ngx-toastr';
import { NgxSpinnerService } from 'ngx-spinner';
import { HttpClient } from '@angular/common/http';
import { HttpHeaders } from '@angular/common/http';
import { Router, ActivatedRoute } from '@angular/router';
import { DomSanitizer } from '@angular/platform-browser';
import { SecretaryDataService } from '../secretary-data.service';
import { DataService } from '../../../../../../storage/data.service';
import { HelperService } from '../../../../../../http/shared/helper.service';
import { PaymentService } from '../../../../../../http/services/payment.service';
import { GeneralService } from '../../../../../../http/services/general.service';
import { SecretaryService } from '../../../../../../http/services/secretary.service';
import { Component, OnInit, ViewChild, ElementRef, HostListener } from '@angular/core';
import { APIConnection } from '../../../../../../http/services/connections/APIConnection';
import { ISecretaryData, ISecretaryWorkHistoryData } from '../../../../../../http/models/secretary.model';
declare var google: any;

@Component({
  selector: 'app-resubmit-secretary-naturalp',
  templateUrl: './resubmit-secretary-naturalp.component.html',
  styleUrls: ['./resubmit-secretary-naturalp.component.scss']
})
export class ResubmitSecretaryNaturalpComponent implements OnInit {

  @ViewChild('content') content: ElementRef;

  url: APIConnection = new APIConnection();

  // secretary details object to register as a natural person...
  secretaryDetails: ISecretaryData = {
    registeredUser: false, nic: '', loggedInUser: '', id: 0, sinFullName: '', tamFullName: '',
    title: '', firstname: '', lastname: '', othername: '', residentialLocalAddress1: '',
    residentialLocalAddress2: '', residentialProvince: null, residentialDistrict: null,
    residentialCity: null, rgnDivision: null, residentialPostCode: '', businessName: '', businessLocalAddress1: '', businessLocalAddress2: '',
    businessProvince: '', businessDistrict: '', businessCity: '', bgnDivision: '', businessPostCode: '', subClauseQualified: '',
    pQualification: '', eQualification: '', wExperience: '', isUnsoundMind: '',
    isInsolventOrBankrupt: '', reason1: '', isCompetentCourt: '', reason2: '', otherDetails: '', workHis: '', isExistSec: '', certificateNo: '',
  };
  secretaryWorkHistory: ISecretaryWorkHistoryData = { id: 0, companyName: '', position: '', from: '', to: '', };

  nic: string;
  index: string;
  localSavedUser: string;
  loggedinUserEmail: string;
  enableStep1Submission = false;
  enableStep2Submission = false;
  enableWorkHistorySubmission = false;
  enableGoToSubmit = false;
  blockBackToForm = false;
  blockSubmit = false;
  cipher_message: string;
  hideAndshowWorkHistory = false;

  // variables for pdf upload...
  workHistory = [];
  uploadDocuments = [];
  application = [];
  eCertificateUploadList = [];
  pCertificateUploadList = [];
  experienceUploadList = [];
  evidenceUploadList = [];
  regCertificateUploadList = [];
  practiceCertificateUploadList = [];
  additional = [];
  description1: string;
  description2: string;
  description3: string;
  description4: string;
  secId: number;

  applicationComment: string;
  professionalComment: string;
  educationalComment: string;
  experienceComment: string;

  businessProvince: string;
  businessDistrict: string;
  businessCity: string;
  bgnDivision: string;

  residentialProvince: string;
  residentialDistrict: string;
  residentialCity: string;
  rgnDivision: string;

  email: any;
  mobile: any;
  tel: any;

  sinFullName = null;
  tamFullName = null;
  tamilelements;
  sinElements;

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

  constructor(public data: DataService,
    private helper: HelperService,
    private secretaryService: SecretaryService,
    private sanitizer: DomSanitizer,
    private route: ActivatedRoute,
    private router: Router,
    private spinner: NgxSpinnerService,
    private httpClient: HttpClient,
    private SecData: SecretaryDataService,
    private general: GeneralService,
    private crToken: PaymentService,
    private snotifyService: ToastrService,
  ) {
    this.secId = this.SecData.getSecId;
    if ((this.secId === undefined)) {
      this.secId = parseInt(localStorage.getItem('secId'), 10);
      this.loadSecretaryData(this.secId);
      this.loadUploadedFile(this.secId);
    }
    else if (!(this.secId === undefined)) {
      localStorage.setItem('secId', this.secId.toString());
      this.changeProgressStatuses(0);
      this.loadSecretaryData(this.secId);
      this.loadUploadedFile(this.secId);
      this.SecData.secId = undefined;
    }
    this.nic = route.snapshot.paramMap.get('nic');
    this.loggedinUserEmail = localStorage.getItem('currentUser');
    this.loggedinUserEmail = this.loggedinUserEmail.replace(/^"(.*)"$/, '$1');
    this.enableWorkHistorySubmission = false;
  }
  ngOnInit() {
    document.getElementById('div1').style.display = 'none';
    document.getElementById('div2').style.display = 'none';
    document.getElementById('div3').style.display = 'none';
    this.sinElements = document.getElementsByClassName('sinhalaname');
    this.tamilelements = document.getElementsByClassName('tamilname');
  }

  /*....below show () functions for the radio buttons validations....*/
  show1() {
    document.getElementById('div1').style.display = 'none';
    this.secretaryDetails['reason1'] = '';
  }
  show2() {
    document.getElementById('div1').style.display = 'block';
  }
  show3() {
    document.getElementById('div2').style.display = 'none';
    this.secretaryDetails['reason2'] = '';
  }
  show4() {
    document.getElementById('div2').style.display = 'block';
  }
  show5() {
    document.getElementById('div3').style.display = 'none';
    this.secretaryDetails['certificateNo'] = '';
  }
  show6() {
    document.getElementById('div3').style.display = 'block';
  }

  // for edit work history...
  showToggle(index) {
    this.workHistory[index]['showEditPaneForWorkHistory'] = !this.workHistory[index]['showEditPaneForWorkHistory'];
    this.hideAndshowWorkHistory = !this.hideAndshowWorkHistory;
    return true;
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


  loadSecretaryData(secId) {
    const data = {
      secId: secId,
    };
    this.secretaryService.secretaryDataLoad(data)
      .subscribe(
        req => {
          if (req['status']) {
            this.sinFullName = req['data']['secretary']['name_si'];
            this.tamFullName = req['data']['secretary']['name_ta'];
            this.email = req['data']['secretary']['email'];
            this.mobile = req['data']['secretary']['mobile'];
            this.tel = req['data']['secretary']['telephone'];
            this.secretaryDetails.title = req['data']['secretary']['title'];
            this.secretaryDetails.firstname = req['data']['secretary']['first_name'];
            this.secretaryDetails.lastname = req['data']['secretary']['last_name'];
            this.secretaryDetails.othername = req['data']['secretary']['other_name'];
            this.secretaryDetails.businessName = req['data']['secretary']['business_name'];
            this.secretaryDetails.subClauseQualified = req['data']['secretary']['which_applicant_is_qualified'];
            this.secretaryDetails.pQualification = req['data']['secretary']['professional_qualifications'];
            this.secretaryDetails.eQualification = req['data']['secretary']['educational_qualifications'];
            this.secretaryDetails.wExperience = req['data']['secretary']['work_experience'];
            this.secretaryDetails.isUnsoundMind = req['data']['secretary']['is_unsound_mind'];
            this.secretaryDetails.isInsolventOrBankrupt = req['data']['secretary']['is_insolvent_or_bankrupt'];
            this.secretaryDetails.isCompetentCourt = req['data']['secretary']['is_competent_court'];
            this.secretaryDetails.otherDetails = req['data']['secretary']['other_details'];
            this.secretaryDetails.isExistSec = req['data']['secretary']['is_existing_secretary'] + '';
            if (this.secretaryDetails.isInsolventOrBankrupt === 'yes') {
              this.secretaryDetails.reason1 = req['data']['secretary']['reason'];
              this.show2();
            } else {
              this.secretaryDetails.reason1 = '';
            }
            if (this.secretaryDetails.isCompetentCourt === 'yes') {
              if (req['data']['secretary']['competent_court_type'] === 'pardoned') {
                this.secretaryDetails.reason2 = 'pardoned';
                this.show4();
              } else if (req['data']['secretary']['competent_court_type'] === 'appeal') {
                this.secretaryDetails.reason2 = 'appeal';
                this.show4();
              }
            } else {
              this.secretaryDetails.reason2 = '';
            }
            if (this.secretaryDetails.isExistSec === '1') {
              this.secretaryDetails.certificateNo = req['data']['certificateNumber'];
              this.show6();
            } else {
              this.secretaryDetails.certificateNo = '';
            }
            this.secretaryDetails.residentialLocalAddress1 = req['data']['secResAddress']['address1'];
            this.secretaryDetails.residentialLocalAddress2 = req['data']['secResAddress']['address2'];
            this.secretaryDetails.residentialPostCode = req['data']['secResAddress']['postcode'];
            this.residentialProvince = req['data']['secResAddress']['province'];
            this.secretaryDetails.residentialProvince = this.residentialProvince;

            this.residentialDistrict = req['data']['secResAddress']['district'];
            this.secretaryDetails.residentialDistrict = this.residentialDistrict;

            this.residentialCity = req['data']['secResAddress']['city'];
            this.secretaryDetails.residentialCity = this.residentialCity;

            this.rgnDivision = req['data']['secResAddress']['gn_division'];
            this.secretaryDetails.rgnDivision = this.rgnDivision;

            if (req['data']['secBusAddress']) {
              this.secretaryDetails.businessLocalAddress1 = req['data']['secBusAddress']['address1'];
              this.secretaryDetails.businessLocalAddress2 = req['data']['secBusAddress']['address2'];
              this.secretaryDetails.businessPostCode = req['data']['secBusAddress']['postcode'];
              this.businessProvince = req['data']['secBusAddress']['province'];
              this.secretaryDetails.businessProvince = this.businessProvince;

              this.businessDistrict = req['data']['secBusAddress']['district'];
              this.secretaryDetails.businessDistrict = this.businessDistrict;

              this.businessCity = req['data']['secBusAddress']['city'];
              this.secretaryDetails.businessCity = this.businessCity;

              this.bgnDivision = req['data']['secBusAddress']['gn_division'];
              this.secretaryDetails.bgnDivision = this.bgnDivision;
            }

            if (req['data']['secretaryWorkHistory']) {
              for (let i in req['data']['secretaryWorkHistory']) {
                const data1 = {
                  id: req['data']['secretaryWorkHistory'][i]['id'],
                  companyName: req['data']['secretaryWorkHistory'][i]['company_name'],
                  position: req['data']['secretaryWorkHistory'][i]['position'],
                  from: req['data']['secretaryWorkHistory'][i]['from'],
                  to: req['data']['secretaryWorkHistory'][i]['to'],
                };
                this.workHistory.push(data1);
              }
            }
          }
          this.secretaryValidationStep1();
          this.secretaryValidationStep2();
        },
        error => {
          console.log(error);
        }
      );
  }


  updateSecretaryData() {
    if (this.secretaryDetails['isInsolventOrBankrupt'] === 'no') {
      this.secretaryDetails['reason1'] = '';
    }
    if (this.secretaryDetails['isCompetentCourt'] === 'no') {
      this.secretaryDetails['reason2'] = 'no';
    }
    const data = {
      id: this.secId,
      nic: this.nic,
      sinFullName: this.sinFullName,
      tamFullName: this.tamFullName,
      email: this.email,
      mobile: this.mobile,
      tel: this.tel,
      isExistSec: this.secretaryDetails['isExistSec'],
      certificateNo: this.secretaryDetails['certificateNo'],
      loggedInUser: this.loggedinUserEmail,
      registeredUser: this.secretaryDetails['registeredUser'],
      title: this.secretaryDetails['title'],
      firstname: this.secretaryDetails['firstname'],
      lastname: this.secretaryDetails['lastname'],
      othername: this.secretaryDetails['othername'],
      residentialLocalAddress1: this.secretaryDetails['residentialLocalAddress1'],
      residentialLocalAddress2: this.secretaryDetails['residentialLocalAddress2'],
      residentialProvince: this.secretaryDetails.residentialProvince.description_en === undefined ? this.residentialProvince : this.secretaryDetails.residentialProvince.description_en,
      residentialDistrict: this.secretaryDetails.residentialDistrict.description_en === undefined ? this.residentialDistrict : this.secretaryDetails.residentialDistrict.description_en,
      residentialCity: this.secretaryDetails.residentialCity.description_en === undefined ? this.residentialCity : this.secretaryDetails.residentialCity.description_en,
      rgnDivision: this.secretaryDetails.rgnDivision.description_en === undefined ? this.rgnDivision : this.secretaryDetails.rgnDivision.description_en,
      residentialPostCode: this.secretaryDetails['residentialPostCode'],
      businessName: this.secretaryDetails['businessName'],
      businessLocalAddress1: this.secretaryDetails['businessLocalAddress1'],
      businessLocalAddress2: this.secretaryDetails['businessLocalAddress2'],
      businessProvince: this.secretaryDetails.businessProvince.description_en === undefined ? this.businessProvince : this.secretaryDetails.businessProvince.description_en,
      businessDistrict: this.secretaryDetails.businessDistrict.description_en === undefined ? this.businessDistrict : this.secretaryDetails.businessDistrict.description_en,
      businessCity: this.secretaryDetails.businessCity.description_en === undefined ? this.businessCity : this.secretaryDetails.businessCity.description_en,
      bgnDivision: this.secretaryDetails.bgnDivision.description_en === undefined ? this.bgnDivision : this.secretaryDetails.bgnDivision.description_en,
      businessPostCode: this.secretaryDetails['businessPostCode'],
      subClauseQualified: this.secretaryDetails['subClauseQualified'],
      pQualification: this.secretaryDetails['pQualification'],
      eQualification: this.secretaryDetails['eQualification'],
      wExperience: this.secretaryDetails['wExperience'],
      isUnsoundMind: this.secretaryDetails['isUnsoundMind'],
      isInsolventOrBankrupt: this.secretaryDetails['isInsolventOrBankrupt'],
      reason1: this.secretaryDetails['reason1'],
      isCompetentCourt: this.secretaryDetails['isCompetentCourt'],
      reason2: this.secretaryDetails['reason2'],
      otherDetails: this.secretaryDetails['otherDetails'],
      workHis: this.workHistory,
    };
    this.secretaryService.secretaryDataUpdate(data)
      .subscribe(
        req => {
          this.secId = req['secId'];
          this.blockBackToForm = false;
          this.changeProgressStatuses(2);
        },
        error => {
          console.log(error);
        }
      );
  }

  addWorkHistoryToArray() {
    const data = {
      id: 0,
      companyName: this.secretaryWorkHistory['companyName'],
      position: this.secretaryWorkHistory['position'],
      from: this.secretaryWorkHistory['from'],
      to: this.secretaryWorkHistory['to'],
    };
    this.workHistory.push(data);
    this.resetAddWorkHistory();
  }
  removeWorkHistoryFromArray(index) {
    if (index > -1) {
      this.workHistory.splice(index, 1);
    }
  }
  resetAddWorkHistory() {
    this.secretaryWorkHistory['companyName'] = '';
    this.secretaryWorkHistory['position'] = '';
    this.secretaryWorkHistory['from'] = '';
    this.secretaryWorkHistory['to'] = '';
  }

  // for download the generated pdf...
  clickDownload() {
    this.secretaryGeneratePDF(this.secId);
  }
  secretaryGeneratePDF(secId) {
    this.spinner.show();
    this.secretaryService.secretaryPDF(secId)
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

  // for uplaod secretary pdf files...
  fileUpload(event, description, docType) {

    let fileList: FileList = event.target.files;
    if (fileList.length > 0) {
      let file: File = fileList[0];
      let fileSize = fileList[0].size;
      let filetype = fileList[0].type;
      if (fileSize > 1024 * 1024 * 4) {
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
      formData.append('secId', this.secId.toString());
      formData.append('description', description);
      formData.append('filename', file.name);

      let headers = new HttpHeaders();
      headers.append('Content-Type', 'multipart/form-data');
      headers.append('Accept', 'application/json');

      let uploadurl = this.url.getSecretaryNaturalFileUploadUrl();
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
            } else if (docType === 'eCertificateUpload') {
              this.eCertificateUploadList.push(datas);
            } else if (docType === 'pCertificateUpload') {
              this.pCertificateUploadList.push(datas);
            } else if (docType === 'experienceUpload') {
              this.experienceUploadList.push(datas);
            }
            this.spinner.hide();
            this.description1 = '';
            this.description2 = '';
            this.description3 = '';
          },
          error => {
            console.log(error);
            this.spinner.hide();
          }
        );
    }
  }

  // for update uplaoded auditor pdf files...
  updateFileUploaded(event, id, description, docType) {

    let fileList: FileList = event.target.files;
    if (fileList.length > 0) {
      let file: File = fileList[0];
      let fileSize = fileList[0].size;
      let filetype = fileList[0].type;
      if (fileSize > 1024 * 1024 * 4) {
        alert('File size should be less than 4 MB');
        return false;
      }
      if (!filetype.match('application/pdf')) {
        alert('Please upload pdf files only');
        return false;
      }

      let formData: FormData = new FormData();

      formData.append('uploadFile', file, file.name);
      formData.append('docId', id);
      formData.append('docType', docType);
      formData.append('secId', this.secId.toString());
      formData.append('description', description);
      formData.append('filename', file.name);

      let headers = new HttpHeaders();
      headers.append('Content-Type', 'multipart/form-data');
      headers.append('Accept', 'application/json');

      let uploadurl = this.url.getSecretaryFileUpdateUploadedUrl();
      this.spinner.show();

      this.httpClient.post(uploadurl, formData, { headers: headers })
        .subscribe(
          (data: any) => {
            this.loadUploadedFile(this.secId);
            this.spinner.hide();
          },
          error => {
            console.log(error);
            this.spinner.hide();
          }
        );
    }
  }

  // for load uplaoded secretary all pdf files...
  loadUploadedFile(secId) {
    const data = {
      secId: secId,
      type: 'individual',
    };
    this.secretaryService.secretaryDocCommentsLoad(data)
      .subscribe(
        req => {
          if (req['status']) {
            if (req['data']['file']) {
              this.application = [];
              this.additional = [];
              this.pCertificateUploadList = [];
              this.eCertificateUploadList = [];
              this.experienceUploadList = [];
              this.evidenceUploadList = [];
              this.regCertificateUploadList = [];

              for (let i in req['data']['file']) {
                const data1 = {
                  id: req['data']['file'][i]['id'],
                  key: req['data']['file'][i]['dockey'],
                  token: req['data']['file'][i]['file_token'],
                  pdfname: req['data']['file'][i]['name'],
                  comment: req['data']['file'][i]['comments'],
                  setkey: req['data']['file'][i]['setkey'],
                  value: req['data']['file'][i]['value'],
                };
                if (req['data']['file'][i]['dockey'] === 'SECRETARY_APPLICATION') {
                  this.application.push(data1);
                } else if (req['data']['file'][i]['dockey'] === 'SECRETARY_EDUCATIONAL_CERTIFICATE') {
                  this.eCertificateUploadList.push(data1);
                } else if (req['data']['file'][i]['dockey'] === 'SECRETARY_PROFESSIONAL_CERTIFICATE') {
                  this.pCertificateUploadList.push(data1);
                } else if (req['data']['file'][i]['dockey'] === 'SECRETARY_EXPERIENCE_CERTIFICATE') {
                  this.experienceUploadList.push(data1);
                } else if (req['data']['file'][i]['dockey'] === 'SECRETARY_EVIDENCE_CERTIFICATE') {
                  this.evidenceUploadList.push(data1);
                } else if (req['data']['file'][i]['dockey'] === 'SECRETARY_CERTIFICATE') {
                  this.regCertificateUploadList.push(data1);
                } else if (req['data']['file'][i]['dockey'] === 'SECRETARY_CERTIFICATE_TO_PRACTICE') {
                  this.practiceCertificateUploadList.push(data1);
                }else {
                  this.additional.push(data1);
                }
              }
              console.log(this.additional);
              this.gotoSubmit(this.application, this.eCertificateUploadList, this.pCertificateUploadList, this.experienceUploadList, this.evidenceUploadList, this.regCertificateUploadList, this.additional, this.practiceCertificateUploadList);
            }
          }
        }
      );
  }

  // for delete the uploaded pdf from the database...
  fileDelete(docId, docType, index) {
    const data = {
      documentId: docId,
    };
    this.spinner.show();
    this.secretaryService.secretaryDeleteUploadedPdf(data)
      .subscribe(
        rq => {
          this.spinner.hide();
          if (index > -1) {
            if (docType === 'applicationUpload') {
              this.application.splice(index, 1);
            } else if (docType === 'eCertificateUpload') {
              this.eCertificateUploadList.splice(index, 1);
            } else if (docType === 'pCertificateUpload') {
              this.pCertificateUploadList.splice(index, 1);
            } else if (docType === 'experienceUpload') {
              this.experienceUploadList.splice(index, 1);
            }
          }
        },
        error => {
          this.spinner.hide();
          console.log(error);
        }
      );
  }

  // for delete the uploaded pdf in resubmit process...
  fileDeleteResubmited(docId, docType, index) {
    const data = {
      documentId: docId,
      type: docType,
    };
    this.spinner.show();
    this.secretaryService.secretaryDeleteUploadedPdfResubmited(data)
      .subscribe(
        rq => {
          this.spinner.hide();
          this.loadUploadedFile(this.secId);
        },
        error => {
          this.spinner.hide();
          console.log(error);
        }
      );
  }

  // for load uplaoded pdf file comments...(not used)
  loadDocComments(secId) {
    const data = {
      secId: secId,
      type: 'individual',
    };
    this.secretaryService.secretaryDocCommentsLoad(data)
      .subscribe(
        req => {
          if (req['status']) {
            for (let i in req['data']['secretaryDoc']) {

              if (req['data']['secretaryDoc'][i]['comments']) {
                if (req['data']['secretaryDoc'][i]['key'] === 'SECRETARY_APPLICATION') {
                  this.applicationComment = req['data']['secretaryDoc'][i]['comments'];
                }
                if (req['data']['secretaryDoc'][i]['key'] === 'SECRETARY_PROFESSIONAL_CERTIFICATE') {
                  this.professionalComment = req['data']['secretaryDoc'][i]['comments'];
                }
                if (req['data']['secretaryDoc'][i]['key'] === 'SECRETARY_EDUCATIONAL_CERTIFICATE') {
                  this.educationalComment = req['data']['secretaryDoc'][i]['comments'];
                }
                if (req['data']['secretaryDoc'][i]['key'] === 'SECRETARY_EXPERIENCE_CERTIFICATE') {
                  this.experienceComment = req['data']['secretaryDoc'][i]['comments'];
                }
              }
            }
            if (this.applicationComment) {
              this.application = [];
            }
            if (this.professionalComment) {
              this.pCertificateUploadList = [];
            }
            if (this.educationalComment) {
              this.eCertificateUploadList = [];
            }
            if (this.experienceComment) {
              this.experienceUploadList = [];
            }
          }
        }
      );
  }

  // for view the uploaded pdf...
  ngOnDownload(token: string): void {
    this.spinner.show();
    this.general.getDocumenttoServer(token, 'CAT_SECRETARY_DOCUMENT')
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

  // for complete resubmition...
  submit() {
    const data = {
      secId: this.secId,
      type: 'individual',
    };
    this.secretaryService.secretaryStatusUpdate(data)
      .subscribe(
        req => {
          if (req['status']) {
            this.router.navigate(['dashboard/selectregistersecretary']);
          } else {
            alert('Error!');
          }
        }
      );
  }

  changeSubClause() {
    if (this.secretaryDetails.subClauseQualified === '') {
      this.secretaryDetails.pQualification = '';
    } else if (this.secretaryDetails.subClauseQualified === '(1)b(i)') {
      this.secretaryDetails.pQualification = 'An attorney at law';
      this.secretaryDetails.eQualification = '';
    } else if (this.secretaryDetails.subClauseQualified === '(1)b(ii)') {
      this.secretaryDetails.pQualification = 'Member of chartered accountants of Sri Lanka';
      this.secretaryDetails.eQualification = '';
    } else if (this.secretaryDetails.subClauseQualified === '(1)b(iii)') {
      this.secretaryDetails.pQualification = 'Member of an association of chartered secretaries and administrators in Sri Lanka';
      this.secretaryDetails.eQualification = '';
    } else if (this.secretaryDetails.subClauseQualified === '(1)b(iv)') {
      this.secretaryDetails.pQualification = 'Member of the association of cost and management accounting';
      this.secretaryDetails.eQualification = '';
    } else if (this.secretaryDetails.subClauseQualified === '(1)b(v)') {
      this.secretaryDetails.pQualification = 'Member of an association or institute approved by the Minister, which provide a course in company law or company secretarial practice';
      this.secretaryDetails.eQualification = '';
    } else if (this.secretaryDetails.subClauseQualified === '(1)b(vi)') {
      this.secretaryDetails.pQualification = 'Any special qualifications in related to company secretarial work form an institution  or other body approval by the minister';
      this.secretaryDetails.eQualification = '';
    } else if (this.secretaryDetails.subClauseQualified === '(1)b(vii)') {
      this.secretaryDetails.pQualification = 'Holding or having held any other position or his being a member of any other body in the public or private sector';
      this.secretaryDetails.eQualification = '';
    } else if (this.secretaryDetails.subClauseQualified === '(1)b(viii)') {
      this.secretaryDetails.pQualification = 'On the day Immediately prior to the date of the coming in to operation of the companies Act No. 17 of 1982 held the office at Secretary or deputy secretary or assistant secretary of a company and satisfied the registrar   of his competence to discharge the duties of a secretary';
      this.secretaryDetails.eQualification = '';
    } else if (this.secretaryDetails.subClauseQualified === '(1)b(ix)') {
      this.secretaryDetails.pQualification = 'Member of the Institute of Certified Management Accountants of Sri Lanka';
      this.secretaryDetails.eQualification = '';
    }
    this.secretaryValidationStep2();
  }

  isUnsoundMindPopUp() {
    if (this.secretaryDetails.isUnsoundMind === 'yes') {
      alert('Warning! You cannot proceed further');
    }
  }

  secretaryValidationStep1() {
    if (
      this.secretaryDetails.title &&
      this.secretaryDetails.firstname &&
      this.secretaryDetails.lastname &&
      this.email && this.validateEmail(this.email) &&
      this.mobile && this.phonenumber(this.mobile) &&
      this.phonenumber(this.tel) &&
      this.secretaryDetails.residentialLocalAddress1 &&
      this.secretaryDetails.residentialLocalAddress2 &&
      this.secretaryDetails.residentialProvince &&
      this.secretaryDetails.residentialCity &&
      this.secretaryDetails.residentialDistrict &&
      this.secretaryDetails.rgnDivision &&
      this.secretaryDetails.residentialPostCode
      // this.secretaryDetails.subClauseQualified
    ) {
      this.enableStep1Submission = true;
      if (this.secretaryDetails.businessName) {
        if (this.secretaryDetails.businessLocalAddress1 &&
          this.secretaryDetails.businessLocalAddress2 &&
          this.secretaryDetails.businessProvince &&
          this.secretaryDetails.businessDistrict &&
          this.secretaryDetails.businessCity &&
          this.secretaryDetails.bgnDivision &&
          this.secretaryDetails.businessPostCode
        ) {
          this.enableStep1Submission = true;
        } else {
          this.enableStep1Submission = false;
        }
      } else {
        if (this.secretaryDetails.businessLocalAddress1 &&
          this.secretaryDetails.businessLocalAddress2 &&
          this.secretaryDetails.businessProvince &&
          this.secretaryDetails.businessDistrict &&
          this.secretaryDetails.businessCity &&
          this.secretaryDetails.bgnDivision &&
          this.secretaryDetails.businessPostCode
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

  secretaryValidationStep2() {
    if (this.secretaryDetails.subClauseQualified) {
      if (this.secretaryDetails.wExperience &&
        this.secretaryDetails.isExistSec &&
        (this.secretaryDetails.isUnsoundMind === 'no') &&
        (this.secretaryDetails.isInsolventOrBankrupt === 'yes' || this.secretaryDetails.isInsolventOrBankrupt === 'no') &&
        (this.secretaryDetails.isCompetentCourt === 'yes' || this.secretaryDetails.isCompetentCourt === 'no')
      ) {
        this.enableStep2Submission = true;
        if (this.secretaryDetails.isInsolventOrBankrupt === 'yes') {
          if (this.secretaryDetails.reason1) {
            this.enableStep2Submission = true;
            if (this.secretaryDetails.isCompetentCourt === 'yes') {
              if (this.secretaryDetails.reason2 === 'pardoned' || this.secretaryDetails.reason2 === 'appeal') {
                this.enableStep2Submission = true;
                this.validateExsistingSec();
              } else {
                this.enableStep2Submission = false;
              }
            } else if (this.secretaryDetails.isCompetentCourt === 'no') {
              if (this.secretaryDetails.reason2 === 'pardoned' || this.secretaryDetails.reason2 === 'appeal') {
                this.enableStep2Submission = false;
              } else {
                this.enableStep2Submission = true;
                this.validateExsistingSec();
              }
            }
          } else {
            this.enableStep2Submission = false;
          }
        } else if (this.secretaryDetails.isInsolventOrBankrupt === 'no') {
          if (this.secretaryDetails.reason1) {
            this.enableStep2Submission = false;
          } else {
            this.enableStep2Submission = true;

            if (this.secretaryDetails.isCompetentCourt === 'yes') {
              if (this.secretaryDetails.reason2 === 'pardoned' || this.secretaryDetails.reason2 === 'appeal') {
                this.enableStep2Submission = true;
                this.validateExsistingSec();
              } else {
                this.enableStep2Submission = false;
              }
            } else if (this.secretaryDetails.isCompetentCourt === 'no') {
              if (this.secretaryDetails.reason2 === 'pardoned' || this.secretaryDetails.reason2 === 'appeal') {
                this.enableStep2Submission = false;
              } else {
                this.enableStep2Submission = true;
                this.validateExsistingSec();
              }
            }
          }
        }
      } else {
        this.enableStep2Submission = false;
      }
    } else {
      if (
        // this.secretaryDetails.pQualification &&
        this.secretaryDetails.eQualification &&
        this.secretaryDetails.wExperience &&
        this.secretaryDetails.isExistSec &&
        // (typeof this.workHistory !== 'undefined' && this.workHistory != null && this.workHistory.length != null && this.workHistory.length > 0) && // for check aray is not null
        (this.secretaryDetails.isUnsoundMind === 'no') &&
        (this.secretaryDetails.isInsolventOrBankrupt === 'yes' || this.secretaryDetails.isInsolventOrBankrupt === 'no') &&
        (this.secretaryDetails.isCompetentCourt === 'yes' || this.secretaryDetails.isCompetentCourt === 'no')
      ) {
        this.enableStep2Submission = true;
        if (this.secretaryDetails.isInsolventOrBankrupt === 'yes') {
          if (this.secretaryDetails.reason1) {
            this.enableStep2Submission = true;
            if (this.secretaryDetails.isCompetentCourt === 'yes') {
              if (this.secretaryDetails.reason2 === 'pardoned' || this.secretaryDetails.reason2 === 'appeal') {
                this.enableStep2Submission = true;
                this.validateExsistingSec();
              } else {
                this.enableStep2Submission = false;
              }
            } else if (this.secretaryDetails.isCompetentCourt === 'no') {
              if (this.secretaryDetails.reason2 === 'pardoned' || this.secretaryDetails.reason2 === 'appeal') {
                this.enableStep2Submission = false;
              } else {
                this.enableStep2Submission = true;
                this.validateExsistingSec();
              }
            }
          } else {
            this.enableStep2Submission = false;
          }
        } else if (this.secretaryDetails.isInsolventOrBankrupt === 'no') {
          if (this.secretaryDetails.reason1) {
            this.enableStep2Submission = false;
          } else {
            this.enableStep2Submission = true;

            if (this.secretaryDetails.isCompetentCourt === 'yes') {
              if (this.secretaryDetails.reason2 === 'pardoned' || this.secretaryDetails.reason2 === 'appeal') {
                this.enableStep2Submission = true;
                this.validateExsistingSec();
              } else {
                this.enableStep2Submission = false;
              }
            } else if (this.secretaryDetails.isCompetentCourt === 'no') {
              if (this.secretaryDetails.reason2 === 'pardoned' || this.secretaryDetails.reason2 === 'appeal') {
                this.enableStep2Submission = false;
              } else {
                this.enableStep2Submission = true;
                this.validateExsistingSec();
              }
            }
          }
        }
      } else {
        this.enableStep2Submission = false;
      }
    }
  }

  validateExsistingSec() {
    if (this.secretaryDetails.isExistSec === '1') {
      if (this.secretaryDetails.certificateNo && this.certnum(this.secretaryDetails.certificateNo)) {
        this.enableStep2Submission = true;
      } else {
        this.enableStep2Submission = false;
      }
    } else if (this.secretaryDetails.isExistSec === '0') {
      this.enableStep2Submission = true;
    }
  }

  // secretaryValidationStep2() {
  //   if (
  //     this.secretaryDetails.pQualification &&
  //     // this.secretaryDetails.eQualification &&
  //     this.secretaryDetails.wExperience &&
  //     // (typeof this.workHistory !== 'undefined' && this.workHistory != null && this.workHistory.length != null && this.workHistory.length > 0) && // for check aray is not null
  //     (this.secretaryDetails.isUnsoundMind === 'yes' || this.secretaryDetails.isUnsoundMind === 'no') &&
  //     (this.secretaryDetails.isInsolventOrBankrupt === 'yes' || this.secretaryDetails.isInsolventOrBankrupt === 'no') &&
  //     (this.secretaryDetails.isCompetentCourt === 'yes' || this.secretaryDetails.isCompetentCourt === 'no')
  //   ) {
  //     this.enableStep2Submission = true;
  //     if (this.secretaryDetails.isInsolventOrBankrupt === 'yes') {
  //       if (this.secretaryDetails.reason1) {
  //         this.enableStep2Submission = true;
  //         if (this.secretaryDetails.isCompetentCourt === 'yes') {
  //           if (this.secretaryDetails.reason2 === 'pardoned' || this.secretaryDetails.reason2 === 'appeal') {
  //             this.enableStep2Submission = true;
  //           } else {
  //             this.enableStep2Submission = false;
  //           }
  //         } else if (this.secretaryDetails.isCompetentCourt === 'no') {
  //           if (this.secretaryDetails.reason2 === 'pardoned' || this.secretaryDetails.reason2 === 'appeal') {
  //             this.enableStep2Submission = false;
  //           } else {
  //             this.enableStep2Submission = true;
  //           }
  //         }
  //       } else {
  //         this.enableStep2Submission = false;
  //       }
  //     } else if (this.secretaryDetails.isInsolventOrBankrupt === 'no') {
  //       if (this.secretaryDetails.reason1) {
  //         this.enableStep2Submission = false;
  //       } else {
  //         this.enableStep2Submission = true;

  //         if (this.secretaryDetails.isCompetentCourt === 'yes') {
  //           if (this.secretaryDetails.reason2 === 'pardoned' || this.secretaryDetails.reason2 === 'appeal') {
  //             this.enableStep2Submission = true;
  //           } else {
  //             this.enableStep2Submission = false;
  //           }
  //         } else if (this.secretaryDetails.isCompetentCourt === 'no') {
  //           if (this.secretaryDetails.reason2 === 'pardoned' || this.secretaryDetails.reason2 === 'appeal') {
  //             this.enableStep2Submission = false;
  //           } else {
  //             this.enableStep2Submission = true;
  //           }
  //         }
  //       }
  //     }
  //   } else {
  //     this.enableStep2Submission = false;
  //   }
  // }

  // validate add work history modal...
  secretaryValidationStep3() {
    if (this.secretaryWorkHistory.companyName &&
      this.secretaryWorkHistory.position &&
      this.secretaryWorkHistory.from &&
      this.secretaryWorkHistory.to
    ) {
      this.enableWorkHistorySubmission = true;
    } else {
      this.enableWorkHistorySubmission = false;
    }
  }

  gotoSubmit(application, eCertificateUploadList, pCertificateUploadList, experienceUploadList, evidenceUploadList, regCertificateUploadList, additional, practiceCertificateUploadList) {

    if (application && eCertificateUploadList && pCertificateUploadList && experienceUploadList && evidenceUploadList && regCertificateUploadList && practiceCertificateUploadList) {

      for (let i in application) {
        if (application[i].setkey === 'DOCUMENT_REQUEST_TO_RESUBMIT') {
          this.enableGoToSubmit = false;
          return false;
        }
        else {
          continue;
        }
      }
      for (let i in eCertificateUploadList) {
        if (eCertificateUploadList[i].setkey === 'DOCUMENT_REQUEST_TO_RESUBMIT') {
          this.enableGoToSubmit = false;
          return false;
        }
        else {
          continue;
        }
      }
      for (let i in pCertificateUploadList) {
        if (pCertificateUploadList[i].setkey === 'DOCUMENT_REQUEST_TO_RESUBMIT') {
          this.enableGoToSubmit = false;
          return false;
        }
        else {
          continue;
        }
      }
      for (let i in experienceUploadList) {
        if (experienceUploadList[i].setkey === 'DOCUMENT_REQUEST_TO_RESUBMIT') {
          this.enableGoToSubmit = false;
          return false;
        }
        else {
          continue;
        }
      }
      for (let i in this.evidenceUploadList) {
        if (evidenceUploadList[i].setkey === 'DOCUMENT_REQUEST_TO_RESUBMIT') {
          this.enableGoToSubmit = false;
          return false;
        }
        else {
          continue;
        }
      }
      for (let i in this.regCertificateUploadList) {
        if (regCertificateUploadList[i].setkey === 'DOCUMENT_REQUEST_TO_RESUBMIT') {
          this.enableGoToSubmit = false;
          return false;
        }
        else {
          continue;
        }
      }
      for (let i in this.practiceCertificateUploadList) {
        if (practiceCertificateUploadList[i].setkey === 'DOCUMENT_REQUEST_TO_RESUBMIT') {
          this.enableGoToSubmit = false;
          return false;
        }
        else {
          continue;
        }
      }
      for (let i in additional) {

        if (additional[i].setkey === 'DOCUMENT_REQUESTED' || additional[i].setkey === 'DOCUMENT_REQUEST_TO_RESUBMIT') {
          this.enableGoToSubmit = false;

          return false;
        }
        else {
          continue;
        }
      }
    }
    this.enableGoToSubmit = true;
    return true;
  }

  // for confirm to going document download step...
  areYouSureYes() {
    if (this.secretaryDetails.reason2 === 'appeal') {
      if (confirm('You cannot proceed further because of appeal')) {
        this.router.navigate(['dashboard/selectregistersecretary']);
      }
    } else {
      this.blockBackToForm = true;
    }
  }
  areYouSureNo() {
    this.blockBackToForm = false;
  }
  // for confirm to complete submit step...
  areYouSureSubmitYes() {
    this.blockSubmit = true;
  }
  areYouSureSubmitNo() {
    this.blockSubmit = false;
  }

  dateValidate(type) {
    if (type === 'from') {
      const date = this.secretaryWorkHistory.from;
      let sendDate: Date = new Date(Date.parse(date.replace(/-/g, ' ')));
      let today = new Date();
      today.setHours(0, 0, 0, 0);
      if (sendDate > today) {
        alert('The work history can\'t be in the future. Please pick another date.');
        this.secretaryWorkHistory.from = '';
      }
      return false;

    } else if (type === 'to') {
      const date = this.secretaryWorkHistory.to;
      let sendDate: Date = new Date(Date.parse(date.replace(/-/g, ' ')));
      let today = new Date();
      today.setHours(0, 0, 0, 0);
      if (sendDate > today) {
        alert('The work history can\'t be in the future. Please pick another date.');
        this.secretaryWorkHistory.to = '';
      }
      return false;
    }
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

  private validateEmail(email) {
    if (!email) { return false; }
    // tslint:disable-next-line:prefer-const
   // let re = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
     // tslint:disable-next-line:prefer-const
  // let re = /^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,3})+$/;

   // let re = /^[A-Za-z0-9]([a-zA-Z0-9]+([_.-][a-zA-Z0-9]+)*)@([A-Za-z0-9]+)(([\.\-]?[a-zA-Z0-9]+)*)\.([A-Za-z]{2,4})$/;
   /**
    *  source : https://stackoverflow.com/questions/201323/how-to-validate-an-email-address-using-a-regular-expression?rq=1
    *
    **/
   let re = /^(?:[a-zA-Z0-9!#$%&'*+/=?^_`{|}~-]+(?:\.[a-zA-Z0-9!#$%&'*+/=?^_`{|}~-]+)*|"(?:[\x01-\x08\x0b\x0c\x0e-\x1f\x21\x23-\x5b\x5d-\x7f]|\\[\x01-\x09\x0b\x0c\x0e-\x7f])*")@(?:(?:[a-z0-9](?:[a-z0-9-]*[a-z0-9])?\.)+[a-z0-9](?:[a-z0-9-]*[a-z0-9])?|\[(?:(?:(2(5[0-5]|[0-4][0-9])|1[0-9][0-9]|[1-9]?[0-9]))\.){3}(?:(2(5[0-5]|[0-4][0-9])|1[0-9][0-9]|[1-9]?[0-9])|[a-z0-9-]*[a-z0-9]:(?:[\x01-\x08\x0b\x0c\x0e-\x1f\x21-\x5a\x53-\x7f]|\\[\x01-\x09\x0b\x0c\x0e-\x7f])+)\])$/;
   // return re.test(String(email).toLowerCase());
    return re.test(email);
  }

  private phonenumber(inputtxt) {
    if (!inputtxt) { return true; }
    // tslint:disable-next-line:prefer-const
    let phoneno = /^\d{10}$/;
    return inputtxt.match(phoneno);
  }

  private certnum(inputtxt) {
    if (!inputtxt) { return true; }
    // tslint:disable-next-line:prefer-const
    let code = /^.{6,}$/;
    return inputtxt.match(code);
  }

}
