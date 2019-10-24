import { ToastrService } from 'ngx-toastr';
import { NgxSpinnerService } from 'ngx-spinner';
import { Component, OnInit } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { HttpHeaders } from '@angular/common/http';
import { DomSanitizer } from '@angular/platform-browser';
import { Router, ActivatedRoute } from '@angular/router';
import { AuditorDataService } from '../auditor-data.service';
import { Item, IBuy } from './../../../../../../http/models/payment';
import { DataService } from '../../../../../../storage/data.service';
import { HelperService } from '../../../../../../http/shared/helper.service';
import { PaymentService } from '../../../../../../http/services/payment.service';
import { GeneralService } from '../../../../../../http/services/general.service';
import { AuditorService } from '../../../../../../http/services/auditor.service';
import { CalculationService } from './../../../../../../http/shared/calculation.service';
import { APIConnection } from '../../../../../../http/services/connections/APIConnection';
import { environment } from '../../../../../../../environments/environment';

@Component({
  selector: 'app-renewal-auditor-naturalp-sl',
  templateUrl: './renewal-auditor-naturalp-sl.component.html',
  styleUrls: ['./renewal-auditor-naturalp-sl.component.scss']
})
export class RenewalAuditorNaturalpSlComponent implements OnInit {

  url: APIConnection = new APIConnection();
  paymentGateway: string = environment.paymentGateway;
  token: string;
  audId: string;

  enableGoToPay = false;
  blockPayment = false;

  application = [];
  prof = [];

  email = '';

  cipher_message: string;

  stepOn = 0;
  processStatus: string;
  progress = {
    stepArr: [
      { label: 'Download Application', icon: 'fa fa-download', status: '' },
      { label: 'Upload Application', icon: 'fa fa-upload', status: '' },
      { label: 'Payments', icon: 'fa fa-money-bill-alt', status: '' },
    ],
    progressPercentage: '16.6%'
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

    this.token = route.snapshot.paramMap.get('token');
    this.checkRegNum();
  }

  ngOnInit() {

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


  // for download the generated pdf...
  clickDownload() {
    this.auditorGeneratePDF(this.token);
  }
  auditorGeneratePDF(token) {
    this.spinner.show();
    this.auditorService.auditorRenewalPDF(token)
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

  getEmail() {

    this.email = localStorage.getItem('currentUser');
    this.email = this.email.replace(/^"(.*)"$/, '$1');
    return this.email;
  }

  // for check registered auditor...
  checkRegNum() {
    const data = {
      token: this.token,
      email: this.getEmail(),
    };
    this.auditorService.auditorIsReg(data)
      .subscribe(
        req => {
          if (req['status']) {
            this.audId = req['audId'];
            this.loadUploadedFile(this.audId);
          }
          else if (req['message'] === 'Unauthorized User') {
            alert('Unauthorized User');
            this.router.navigate(['/dashboard/home']);
          }
          else{
            alert('Invalid token or unregistered auditor');
            this.router.navigate(['/dashboard/home']);
          }
        },
        error => {
          console.log(error);
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
      formData.append('audId', this.audId);
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
            if (docType === 'renewalFormUpload') {
              this.application.push(datas);
            }
            else if (docType === 'renewalPQUpload') {
              this.prof.push(datas);
            }
            this.gotoPay();
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
            if (docType === 'renewalFormUpload') {
              this.application.splice(index, 1);
            }
            else if (docType === 'renewalPQUpload') {
              this.prof.splice(index, 1);
            }
            this.gotoPay();
          }
        },
        error => {
          this.spinner.hide();
          console.log(error);
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
                const data1 = {
                  id: req['data']['file'][i]['id'],
                  key: req['data']['file'][i]['dockey'],
                  token: req['data']['file'][i]['file_token'],
                  pdfname: req['data']['file'][i]['name'],
                };
                if ((req['data']['file'][i]['dockey'] === 'AUDITOR_RENEWAL_APPLICATION') && (req['data']['file'][i]['setkey'] === 'DOCUMENT_PENDING')) {
                  this.application.push(data1);
                }
                else if ((req['data']['file'][i]['dockey'] === 'AUDITOR_RENEWAL_PROF_QUALIFICATION') && (req['data']['file'][i]['setkey'] === 'DOCUMENT_PENDING')) {
                  this.prof.push(data1);
                }
              }
              this.gotoPay();
            }
          }
        }
      );
  }

  getCipherToken() {
    if (!this.audId) { return this.router.navigate(['dashboard/selectregisterauditor']); }
    const item: Array<Item> = [{
      fee_type: 'PAYMENT_RENEWAL_INDIVIDUAL_AS_AUDITORS',
      description: 'Auditor individual renewal',
      quantity: 1,
    }];
    const buy: IBuy = {
      module_type: 'MODULE_AUDITOR_RENEWAL',
      module_id: this.audId.toString(),
      description: 'Auditor individual renewal',
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

  gotoPay() {
    if ((typeof this.application !== 'undefined' && this.application != null && this.application.length != null && this.application.length > 0) && (typeof this.prof !== 'undefined' && this.prof != null && this.prof.length != null && this.prof.length > 0)) {
      this.enableGoToPay = true;
    } else {
      this.enableGoToPay = false;
    }
  }

  // for confirm to complete payment step...
  areYouSurePayYes() {
    this.getCipherToken();
    this.blockPayment = true;
  }
  areYouSurePayNo() {
    this.blockPayment = false;
  }


}
