import { ToastrService } from 'ngx-toastr';
import { NgxSpinnerService } from 'ngx-spinner';
import { Component, OnInit } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { HttpHeaders } from '@angular/common/http';
import { DomSanitizer } from '@angular/platform-browser';
import { Router, ActivatedRoute } from '@angular/router';
import { AuditorDataService } from '../auditor-data.service';
import { DataService } from '../../../../../../storage/data.service';
import { HelperService } from '../../../../../../http/shared/helper.service';
import { AuditorService } from '../../../../../../http/services/auditor.service';
import { GeneralService } from '../../../../../../http/services/general.service';
import { APIConnection } from '../../../../../../http/services/connections/APIConnection';

@Component({
  selector: 'app-renewal-resubmit-auditor-naturalp-sl',
  templateUrl: './renewal-resubmit-auditor-naturalp-sl.component.html',
  styleUrls: ['./renewal-resubmit-auditor-naturalp-sl.component.scss']
})
export class RenewalResubmitAuditorNaturalpSlComponent implements OnInit {

  url: APIConnection = new APIConnection();

  token: string;
  audId: string;

  enableGoToSubmit = false;
  blockSubmit = false;

  application = [];
  prof = [];
  comments = [];

  email = '';

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
    private snotifyService: ToastrService,

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
            this.loadComments(this.audId);
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
      formData.append('audId', this.audId.toString());
      formData.append('description', description);
      formData.append('filename', file.name);

      let headers = new HttpHeaders();
      headers.append('Content-Type', 'multipart/form-data');
      headers.append('Accept', 'application/json');

      let uploadurl = this.url.getAuditorFileUpdateUploadedUrl();
      this.spinner.show();

      this.httpClient.post(uploadurl, formData, { headers: headers })
        .subscribe(
          (data: any) => {
            this.loadUploadedFile(this.audId);
            this.spinner.hide();
          },
          error => {
            console.log(error);
            this.spinner.hide();
          }
        );
    }
  }

  // for load uplaoded auditor all pdf files...
  loadUploadedFile(audId) {
    const data = {
      audId: audId,
      type: 'individual',
    };
    this.auditorService.auditorDocCommentsLoad(data)
      .subscribe(
        req => {
          if (req['status']) {
            if (req['data']['file']) {
              this.application = [];
              this.prof = [];
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
                if ((req['data']['file'][i]['dockey'] === 'AUDITOR_RENEWAL_APPLICATION')) {
                  if ((req['data']['file'][i]['setkey'] === 'DOCUMENT_REQUEST_TO_RESUBMIT') || (req['data']['file'][i]['setkey'] === 'DOCUMENT_PENDING')) {
                    this.application.push(data1);
                  }
                }
                else if ((req['data']['file'][i]['dockey'] === 'AUDITOR_RENEWAL_PROF_QUALIFICATION')) {
                  if ((req['data']['file'][i]['setkey'] === 'DOCUMENT_REQUEST_TO_RESUBMIT') || (req['data']['file'][i]['setkey'] === 'DOCUMENT_PENDING')) {
                    this.prof.push(data1);
                  }
                }
              }
              this.gotoSubmit(this.application, this.prof);
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

  // for delete the uploaded pdf in resubmit process...
  fileDeleteResubmited(docId, docType, index) {
    const data = {
      documentId: docId,
    };
    this.spinner.show();
    this.auditorService.auditorDeleteUploadedPdfResubmited(data)
      .subscribe(
        rq => {
          this.spinner.hide();
          this.loadUploadedFile(this.audId);
        },
        error => {
          this.spinner.hide();
          console.log(error);
        }
      );
  }

  // for load main comments...
  loadComments(audId) {
    const data = {
      audId: audId,
      type: 'individual',
    };
    this.auditorService.auditorCommentsLoad(data)
      .subscribe(
        req => {
          if (req['status']) {
            if (req['data']['auditorComment']) {
              for (let i in req['data']['auditorComment']) {
                if (req['data']['auditorComment'][i]['status'] === 'AUDITOR_RENEWAL_REQUEST_TO_RESUBMIT') {
                  const data1 = {
                    id: req['data']['auditorComment'][i]['id'],
                    comment: req['data']['auditorComment'][i]['comments'],
                    createdAt: req['data']['auditorComment'][i]['created_at'],
                  };
                  this.comments.push(data1);
                }
              }
            }
          }
        },
        error => {
          console.log(error);
        }
      );
  }

  submit() {


    const data = {

      audId: this.audId,
      type: 'individual'


    };


    this.auditorService.auditorRenewalReSubmit(data)
      .subscribe(
        req => {

          // localStorage.removeItem('storage1');
          this.router.navigate(['dashboard/selectregisterauditor']);

        },
        error => {
          console.log(error);
        }
      );

  }

  // submit() {
  //   this.router.navigate(['dashboard/selectregisterauditor']);
  // }

  gotoSubmit(application, prof) {

    if (application) {
      for (let i in application) {
        if (application[i].setkey === 'DOCUMENT_REQUEST_TO_RESUBMIT') {
          this.enableGoToSubmit = false;
          return false;
        }
        else {
          continue;
        }
      }
    }
    if (prof) {
      for (let i in prof) {
        if (prof[i].setkey === 'DOCUMENT_REQUEST_TO_RESUBMIT') {
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

  // for confirm to complete payment step...
  areYouSureSubmitYes() {
    this.blockSubmit = true;
  }
  areYouSureSubmitNo() {
    this.blockSubmit = false;
  }


}
