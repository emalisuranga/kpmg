import { Component, OnInit, AfterViewInit, ElementRef } from '@angular/core';
import { DataService } from '../../../../../../storage/data.service';
import { GeneralService } from '../../../../../../http/services/general.service';
import { IDebentureRow } from '../../../../../../http/models/debentures.model';
import { IssueOfDebenturesService } from '../../../../../../http/services/issue-of-debentures.service';
import { DebenturesDataService } from '../debentures-data.service';
import { HttpHeaders } from '@angular/common/http';
import { HttpClient } from '@angular/common/http';
import { NgxSpinnerService } from 'ngx-spinner';
import { APIConnection } from '../../../../../../http/services/connections/APIConnection';
import { HelperService } from '../../../../../../http/shared/helper.service';
import { PaymentService } from '../../../../../../http/services/payment.service';
import { Router, ActivatedRoute } from '@angular/router';
import { CalculationService } from './../../../../../../http/shared/calculation.service';
import { ISignedStakeholder } from '../../issue-of-shares-new/models/form6.model';

@Component({
  selector: 'app-issue-of-debentures-resubmit',
  templateUrl: './issue-of-debentures-resubmit.component.html',
  styleUrls: ['./issue-of-debentures-resubmit.component.scss']
})
export class IssueOfDebenturesResubmitComponent implements OnInit {

  formattedTodayValue = '';
  progress = {

    stepArr: [
      { label: 'Add new Debenture', icon: 'fa fa-list-ol', status: 'active' },
      { label: 'Download Documents', icon: 'fa fa-download', status: '' },
      { label: 'Upload Documents', icon: 'fa fa-upload', status: '' },
      { label: 'Payments', icon: 'fa fa-money-bill-alt', status: '' },
    ],

    progressPercentage: '12.5%'

  };

  companyId: number;
  reqId: number;
  preReqId: number;
  document_confirm = false;
  status: string;
  type: string;
  companyName: string;
  companyRegno: string;
  dateofissue: any;
  cipher_message: string;
  description: string;
  date = new Date();
  stepOn = 0;
  validationMessage = '';
  validationMessageSubmit = '';
  email = ' ';
  validationMessageType = false;
  validDeb = false;
  validDebEdit = false;
  enableStep1Submission = true;
  hideAndshow = false;
  blockBackToForm = false;
  blockBackToForm1 = false;
  blockPayment = false;
  validNewDebenture = false;
  addNewDebenture = false;
  enableGoToPay = false;
  routetoDashboard: boolean;
  storage1: any;
  storage2: any;
  application = [];
  aditionalDocumentList = [];
  additional = [];
  preApprovedDeb = [];
  newDebentures = [];
  externalGlobalComment = '';
  url: APIConnection = new APIConnection();
  newDebenture: IDebentureRow = { id: 0, showEditPaneForMemb: false, totalamountsecured: null, series: null, amount: null, description: null, nameoftrustees: null, dateofcoveringdead: null, dateofresolution: null, dateofissue: null };

  signing_party_designation = '';
  singning_party_name = '';

  signedDirectors: Array<ISignedStakeholder> = [];
  signedSecs: Array<ISignedStakeholder> = [];
  signedSecFirms: Array<ISignedStakeholder> = [];

  constructor(
    private router: Router,
    public calculation: CalculationService,
    private crToken: PaymentService,
    private helper: HelperService,
    private spinner: NgxSpinnerService,
    private httpClient: HttpClient,
    public data: DataService,
    private general: GeneralService,
    // tslint:disable-next-line:no-shadowed-variable
    private IssueOfDebenturesService: IssueOfDebenturesService,
    private DebData: DebenturesDataService
  ) {

    this.status = this.DebData.getStatus;
    this.companyId = this.DebData.getComId;
    this.reqId = this.DebData.getReqId;
    this.loadProcesingList();
    this.loadUploadedFile();
    // this.loadPreApproved();
    this.document_confirm = true;
  }

  ngOnInit() {
    if (!this.DebData.getNavigatetoDashboard){
      this.router.navigate(['dashboard/home']);
    }
    this.formattedTodayValue = this.getFormatedToday();
  }


  changeProgressStatuses(newStatus = 1) {
    this.formattedTodayValue = this.getFormatedToday();
    this.stepOn = newStatus;
    this.progress.progressPercentage = (this.stepOn >= 4) ? (12.5 * 2 + this.stepOn * 25) + '%' : (12.5 + this.stepOn * 25) + '%';
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

  getEmail() {

    this.email = localStorage.getItem('currentUser');
    this.email = this.email.replace(/^"(.*)"$/, '$1');
    return this.email;
  }

  // Load previous approved debentures record on bganing...
  loadPreApproved() {

    this.formattedTodayValue = this.getFormatedToday();

    const data = {
      id: this.companyId,
    };
    this.IssueOfDebenturesService.loadPreApproved(data)
      .subscribe(
        req => {
          if (undefined !== req['status'] && req['status']) {
            if (req['debentures']) {
              this.preApprovedDeb = [];
              for (let i in req['debentures']) {
                const data1 = {
                  id:  req['id'][i]['totalAmountSecured'],
                  totalAmountSecured: req['debentures'][i]['totalAmountSecured'],
                  series: req['debentures'][i]['series'],
                  amount: req['debentures'][i]['amount'],
                  description: req['debentures'][i]['description'],
                  nameOfTrustees: req['debentures'][i]['nameOfTrustees'],
                  dateOfCoveringDead: req['debentures'][i]['dateOfCoveringDead'],
                  dateOfResolution: req['debentures'][i]['dateOfResolution'],
                  dateOfIssue: req['debentures'][i]['dateOfIssue'],
                };
                this.preApprovedDeb.push(data1);
              }
            }
          }

        },
        error => {
          console.log(error);
        }
      );
  }

  resetSignParty() {
    this.singning_party_name = '';
  }

  // Load procesing debentures list on beganing...
  loadProcesingList() {

    const data = {
      id: this.companyId,
      type: 'resubmit',
      status: this.DebData.getStatus,
    };
    this.IssueOfDebenturesService.loadProcesingList(data)
      .subscribe(
        req => {
          if (req['status']) {
            if (req['processingdeb']) {
              this.newDebentures = [];
              for (let i in req['processingdeb']) {

                const data1 = {
                  showEditPaneForMemb: false,
                  id: req['processingdeb'][i]['id'],
                  totalamountsecured: req['processingdeb'][i]['totalAmountSecured'],
                  series: req['processingdeb'][i]['series'],
                  amount: req['processingdeb'][i]['amount'],
                  description: req['processingdeb'][i]['description'],
                  nameoftrustees: req['processingdeb'][i]['nameOfTrustees'],
                  dateofcoveringdead: req['processingdeb'][i]['dateOfCoveringDead'],
                  dateofresolution: req['processingdeb'][i]['dateOfResolution'],
                  dateofissue: req['processingdeb'][i]['dateOfIssue'],
                };
                this.newDebentures.push(data1);
              }
            }
            this.signedDirectors = req['directors'];
            this.signedSecs = req['secs'];
            this.signedSecFirms = req['sec_firms'];
            this.singning_party_name = req['signed_party_id'];
            this.signing_party_designation = req['signing_party_designation'];
          }
          this.externalGlobalComment = req['external_global_comment'];
        },
        error => {
          console.log(error);
        }
      );
  }

  // Hide and show list
  showToggle(index = 0) {
    this.newDebentures[index]['showEditPaneForMemb'] = !this.newDebentures[index]['showEditPaneForMemb'];
    this.hideAndshow = !this.hideAndshow;
    return true;
  }

  // Edit data in debenture list...
  editMembDataArray(i) {

    const data = {
      showEditPaneForMemb: false,
      id: this.newDebentures[i].id,
      totalamountsecured: this.newDebentures[i].totalamountsecured,
      series: this.newDebentures[i].series,
      amount: this.newDebentures[i].amount,
      description: this.newDebentures[i].description,
      nameoftrustees: this.newDebentures[i].nameoftrustees,
      dateofcoveringdead: this.newDebentures[i].dateofcoveringdead,
      dateofresolution: this.newDebentures[i].dateofresolution,
      dateofissue: this.newDebentures[i].dateofissue
    };
    this.newDebentures.splice(i, 1, data);
    this.validationMessageSubmit = '';
    this.hideAndshow = false;
  }

  // Validate debenture record while in list(Toggle status)...
  validateDebentureEdit(i) {

    if (!
      (
        this.newDebentures[i].totalamountsecured && parseFloat(this.newDebentures[i].totalamountsecured.toString()) &&
        // this.newDebentures[i].series &&
        this.onlynumbersandchar(this.newDebentures[i].series) &&
        this.newDebentures[i].amount && parseFloat(this.newDebentures[i].amount.toString()) &&
        this.newDebentures[i].description && this.onlynumbersandchar(this.newDebentures[i].description) &&
        // this.newDebentures[i].nameoftrustees && this.onlychars(this.newDebentures[i].nameoftrustees) &&
       // this.newDebentures[i].dateofcoveringdead &&
       // this.newDebentures[i].dateofresolution &&
        this.newDebentures[i].dateofissue
      )
    ) {
      this.validationMessage = 'Please fill all  required fields denoted by asterik(*)';
      this.validDebEdit = false;
      return false;

    } else {
      this.validationMessage = '';
      this.validationMessageSubmit = '';
      this.validDebEdit = true;
      return true;
    }
  }

  // Validate full list when submit...
  validateDebentureList() {

    this.enableStep1Submission = true;
    // tslint:disable-next-line:triple-equals
    if ((typeof this.newDebentures === 'undefined' || this.newDebentures == null || this.newDebentures.length == null || this.newDebentures.length == 0)) {
      this.validationMessageType = true;
      this.enableStep1Submission = false;
    } else {
      for (let i in this.newDebentures) {
        if (!(
          this.newDebentures[i].totalamountsecured && Number(this.newDebentures[i].totalamountsecured) &&
          // this.newDebentures[i].series &&
          this.onlynumbersandchar(this.newDebentures[i].series) &&
          this.newDebentures[i].amount && Number(this.newDebentures[i].amount) &&
          // this.newDebentures[i].nameoftrustees && this.onlychars(this.newDebentures[i].nameoftrustees) &&
          this.newDebentures[i].description && this.onlynumbersandchar(this.newDebentures[i].description) &&
        //  this.newDebentures[i].dateofcoveringdead &&
        //  this.newDebentures[i].dateofresolution &&
          this.newDebentures[i].dateofissue
        )) {
          this.enableStep1Submission = false;
          this.validationMessageType = false;
        }
      }
    }
    this.stepOneSubmission();
  }

  // Check status before submit...
  stepOneSubmission() {
    if (this.enableStep1Submission) {
      this.debenturesDetailsSubmit();
    } else {
      if (this.validationMessageType) {
        this.blockBackToForm = false;
        this.validationMessageSubmit = 'please add at least one debenture record before continue...';
      } else {
        this.blockBackToForm = false;
        this.validationMessageSubmit = 'Please fill correctly all  required fields denoted by asterik(*)';
      }
    }
  }

  // Debentures details submit...
  debenturesDetailsSubmit() {

    console.log(this.newDebentures);
   // return;

    const data = {
      comId: this.companyId,
      email: this.email,
      debenture_array: this.newDebentures,
      type: 'resubmit',
      reqId: this.reqId,
      signing_party_designation : this.signing_party_designation,
      signed_party_id: this.singning_party_name,
    };

    this.IssueOfDebenturesService.debenturesSubmit(data)
      .subscribe(
        req => {
          if (req['status']) {
            this.blockBackToForm = false;
            this.changeProgressStatuses(1);
          }
        },
        error => {
          this.blockBackToForm = false;
          console.log(error);
        }
      );
  }

  private onlynumbersandchar(inputtxt) {
    if (!inputtxt) { return true; }
    // tslint:disable-next-line:prefer-const
    let numchar = /^[0-9a-zA-Z\s]*$/;
    return inputtxt.match(numchar);
  }

  private onlychars(inputtxt) {
    if (!inputtxt) { return true; }
    // tslint:disable-next-line:prefer-const
    let char = /^[a-zA-Z\s]*$/;
    return inputtxt.match(char);
  }

  // issue of debentures Form 10A download
  issueofDebenturesFormGeneratePDF() {

    const data = {
      comId: this.companyId,
      email: this.getEmail(),
    };
    this.IssueOfDebenturesService.getIssueofDebenturesFormPDFService(data)
      .subscribe(
        response => {
          this.helper.download(response);
        },
        error => {
          console.log(error);
        }
      );
  }

  // for uplaod issue of debentures pdf files...
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
      formData.append('comId', this.companyId.toString());
      formData.append('description', description);
      formData.append('filename', file.name);

      let headers = new HttpHeaders();
      headers.append('Content-Type', 'multipart/form-data');
      headers.append('Accept', 'application/json');

      let uploadurl = this.url.getIssueofDebentureFileUploadUrl();
      this.spinner.show();

      this.httpClient.post(uploadurl, formData, { headers: headers })
        .subscribe(
          (data: any) => {
            this.loadUploadedFile();
            this.description = '';
            this.spinner.hide();
          },
          error => {
            this.spinner.hide();
          }
        );
    }
  }

  // For loading uploaded file...
  loadUploadedFile() {

    const data = {
      comId: this.companyId,
      type: 'resubmit'
    };
    this.IssueOfDebenturesService.issueofdebenturesFiles(data)
      .subscribe(
        req => {
          if (req['status']) {
            if (req['data']['file']) {
              this.application = [];
              this.aditionalDocumentList = [];
              this.additional = [];
              for (let i in req['data']['file']) {
                const data1 = {
                  id: req['data']['file'][i]['id'],
                  name: req['data']['file'][i]['docname'],
                  key: req['data']['file'][i]['docKey'],
                  token: req['data']['file'][i]['file_token'],
                  pdfname: req['data']['file'][i]['name'],
                  description: req['data']['file'][i]['description'],
                  company_document_id: req['data']['file'][i]['company_document_id'],
                  comments: req['data']['file'][i]['comments'],
                  value: req['data']['file'][i]['value'],
                  file_description: req['data']['file'][i]['file_description'],
                  setKey: req['data']['file'][i]['setKey']
                };
                // tslint:disable-next-line:triple-equals
                if (req['data']['file'][i]['docKey'] == 'ISSUE_OF_DEBENTURES_FORM10A') {
                  this.application.push(data1);

                // tslint:disable-next-line:triple-equals
                } else if (req['data']['file'][i]['docKey'] == 'ISSUE_OF_DEBENTURES_ADDITIONAL_DOCUMENT') {
                  this.aditionalDocumentList.push(data1);
                }
                else {
                  this.additional.push(data1);
                }
              }
              this.gotoPay(this.application, this.aditionalDocumentList, this.additional);
            }
          }
        }
      );
  }

  // for view the uploaded pdf...
  ngOnDownload(token: string): void {
    this.spinner.show();
    this.general.getDocumenttoServer(token)
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

  // for uplaod updated issue of debentures pdf files...
  fileUploadUpdate(event, id, description, docType) {

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
      formData.append('docId', id);
      formData.append('comId', this.companyId.toString());
      formData.append('description', description);
      formData.append('filename', file.name);

      let headers = new HttpHeaders();
      headers.append('Content-Type', 'multipart/form-data');
      headers.append('Accept', 'application/json');

      let uploadurl = this.url.getIssueofDebenturesUpdatedFileUploadUrl();
      this.spinner.show();


      this.httpClient.post(uploadurl, formData, { headers: headers })
        .subscribe(
          (data: any) => {
            this.loadUploadedFile();
            this.spinner.hide();
          },
          error => {
            console.log(error);
            this.spinner.hide();
          }
        );
    }
  }

  // for delete the uploaded pdf from the database...
  fileDeleteUpdate(docId, docType, index) {

    this.spinner.show();
    const data = {
      documentId: docId,
      type: docType,
    };

    this.IssueOfDebenturesService.issueofDebenturesDeleteUploadedUpdatePdf(data)
      .subscribe(
        response => {
          if (response['status']) {

            this.loadUploadedFile();
            this.spinner.hide();
          }
        },
        error => {
          this.spinner.hide();
          console.log(error);
        }
      );
  }

  gotoPay(application, aditionalDocumentList, additional) {

    if (application) {

      for (let i in application) {

        if (application[i].setKey === 'DOCUMENT_REQUEST_TO_RESUBMIT') {
          this.enableGoToPay = false;

          return false;
        }
        else {
          continue;
        }
      }
      for (let i in additional) {

        if (additional[i].setKey === 'DOCUMENT_REQUESTED' || additional[i].setKey === 'DOCUMENT_REQUEST_TO_RESUBMIT') {
          this.enableGoToPay = false;

          return false;
        }
        else {
          continue;
        }
      }
      for (let i in aditionalDocumentList) {

        if (aditionalDocumentList[i].setKey === 'DOCUMENT_REQUEST_TO_RESUBMIT') {
          this.enableGoToPay = false;

          return false;
        }
        else {
          continue;
        }
      }
    }

    this.enableGoToPay = true;
    return true;
  }

  // For complete issue of shares resubmit process
  issueofDebenturesReSubmit() {

    const data = {
      reqId: this.reqId,
    };

    this.IssueOfDebenturesService.issueofdebenturesReSubmit(data)
      .subscribe(
        req => {

          this.router.navigate(['/dashboard/home']);

        },
        error => {
          console.log(error);
        }
      );

  }

  areYouSureYes() {
    this.blockBackToForm = true;
  }
  areYouSureNo() {
    this.blockBackToForm = false;
  }

  areYouSureYes1() {
    this.blockBackToForm1 = true;
  }
  areYouSureNo1() {
    this.blockBackToForm1 = false;
  }

  private getFormatedToday() {
    var d = new Date(),
        month = '' + (d.getMonth() + 1),
        day = '' + d.getDate(),
        year = d.getFullYear();

    if (month.length < 2) {
      month = '0' + month;
    }
    if (day.length < 2) {
      day = '0' + day;
    }

    return [year, month, day].join('-').toString();
}

}
