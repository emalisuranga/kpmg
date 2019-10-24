import { IReqDocument } from './../../../../../../http/models/file.model';
import { Component, OnInit, ViewChild, ElementRef } from '@angular/core';
import { FormDataService } from '../data/formData.service';
import { GeneralService } from 'src/app/http/services/general.service';
import { IDocGroup } from 'src/app/http/models/doc.model';
import { HelperService } from 'src/app/http/shared/helper.service';
import { NgxSpinnerService } from 'ngx-spinner';
import { DocumentsService } from 'src/app/http/services/documents.service';
import { AuthService } from 'src/app/http/shared/auth.service';
import swal from 'sweetalert2';
import { HttpClient, HttpEventType, HttpResponse } from '@angular/common/http';
import { FormGroup, FormBuilder, FormControl, Validators } from '@angular/forms';
import { NameChangeService } from 'src/app/http/services/name-change.service';
import { IBuyDetails } from 'src/app/storage/ibuy-details';
import { Router } from '@angular/router';
import { NameResarvationService } from 'src/app/http/services/name-resarvation.service';
import { ICompanyCommentWith, IUploadOtherDocs } from 'src/app/http/models/recervationdata.model';
import { ToastrService } from 'ngx-toastr';
import { Item } from 'src/app/http/models/payment';
import { HttpHeaders } from '@angular/common/http';
import {APIConnection} from 'src/app/http/services/connections/APIConnection';
import { IMember } from '../../../../../../http/models/general.model';

@Component({
  selector: 'app-select-name-change',
  templateUrl: './select-name-change.component.html',
  styleUrls: ['./select-name-change.component.scss']
})
export class SelectNameChangeComponent implements OnInit {

  url: APIConnection = new APIConnection();

  @ViewChild('form') FileInputVariable: ElementRef;
  @ViewChild('formOther') FileInputVariable2: ElementRef;

  @ViewChild('uploadbtnOther')
  uploadbtnOther: ElementRef;

  public nameChangeGroup: FormGroup;
  public docs: IDocGroup;
  public reqDocument: Array<IReqDocument> = [];
  percentDone: Array<number> = [];
  uploadSuccess: boolean;
  fileName: Array<string> = [];
  public comments: Array<ICompanyCommentWith> = [];
  public id: number;
  public i = 1;
  public fileToken: Array<number> = [];
  public reqcount = 0;

  public member: IMember;

 prior_approval_letter_name = '';
 prior_approval_letter_token = '';
 company_reservation_at = '';
 company_exp_at = '';
 public date: Date = new Date();
 public min = new Date(2018, 1, 12, 10, 30);
  public max = new Date(this.date.getUTCFullYear(), this.date.getUTCMonth(), this.date.getUTCDate(), this.date.getUTCHours(), this.date.getUTCMinutes());
 resolution_date_set = false;
 resolution_date = '';
 selectCat = '';

  public uploadCount = 0;
  showandhide: boolean = false;
  public items = 0;
  public uploadCountAndMulti = 0;

  private Idcn: string;
  private newRefid: string;
  oldRefId: string;
  private resDate: string;

  private item: Array<Item>;
  public MaxDate: number;
  public MaxAmount: number;
  other_doc_name = '';

  otherDocsForResubmit = {name: '' , key: '', id: null };

  court_status = '';
  court_name = '';
  court_case_no = '';
  court_date = '';
  court_penalty = '';
  court_period = '';
  court_discharged = '';
  request_status = '';
  validateCourtSectionFlag = false;

  penalty_charge = 0;

  formattedTodayValue = '';

  otherUploadedList: IUploadOtherDocs = { docs: [] };

  constructor(
    private helper: HelperService,
    private http: HttpClient,
    public formdataservice: FormDataService,
    private general: GeneralService,
    private spinner: NgxSpinnerService,
    private docService: DocumentsService,
    private auth: AuthService,
    private iBy: IBuyDetails,
    private router: Router,
    public rec: NameResarvationService,
    public nameChange: NameChangeService,
    private snotifyService: ToastrService,
  ) { }

  ngOnInit() {
    this.Idcn = this.formdataservice.getIdNumber;
    this.newRefid = this.formdataservice.getNewRefNumber;
    localStorage.removeItem('nameChangeCompanyId');
    localStorage.setItem('nameChangeCompanyId', JSON.stringify(this.newRefid));
    this.resDate = this.formdataservice.getResDate;

    this.formattedTodayValue = this.getFormatedToday();

    this.general.getSetting('PENALTY_LIMITED_DATE', 'key').subscribe(req => {
      this.MaxDate = req;
    });
    this.spinner.show();
    this.rec.getFileResubmitData(Number(this.newRefid))
      .subscribe(
        req => {
          this.spinner.hide();
        },
        error => {
          this.spinner.hide();
        }
      );
    if (this.formdataservice.getResubmit) {
      this.getResubmitData(this.newRefid);
    } else {
      this.getDocumentfield();
    }
  }

  private getOtherDocs() {
    const data = {
      company_id: this.newRefid,
    };

    // load Company data from the server
    this.general.getNameChangeOtherDocList(data)
      .subscribe(
        req => {
          console.log(req);
          this.spinner.hide();
          this.formattedTodayValue = this.getFormatedToday();
          this.otherUploadedList.docs = req['docs'];

          console.log(this.otherUploadedList.docs);
          this.court_status = req['court_status'];
          this.court_name = req['court_name'];
          this.court_case_no = req['court_case_no'];
          this.court_date = req['court_date'];
          this.court_penalty = req['court_penalty'];
          this.court_period = req['court_period'];
          this.court_discharged = req['court_discharged'];
          this.request_status = req['status'];
          this.penalty_charge = req['penalty_charge'];

          this.validateCourtSection();
        }
      );

  }


  private updateCourtOrderDetails() {
    const data = {
      new_company_id: this.newRefid,
      court_status: this.court_status,
      court_name: this.court_name,
      court_date: this.court_date,
      court_case_no: this.court_case_no,
      court_penalty: this.court_penalty,
      court_period: this.court_period,
      court_discharged: this.court_discharged

    };
    this.spinner.show();

    // load Company data from the server
    this.general.updateCourtOrderDetails(data)
      .subscribe(
        req => {
          this.spinner.hide();

          if (this.request_status !== 'COMPANY_NAME_CHANGE_REQUEST_TO_RESUBMIT') {
            this.router.navigate(['reservation/payment']);
          }

        }
      );

  }


  getDocumentfield(): void {
    this.spinner.show();
    this.showandhide = false;
    this.general.getDocFeild(20, 'NAME_CHANGE', Number(this.newRefid) )
      .subscribe(
        req => {
          this.docs = req['collection'];

          this.prior_approval_letter_name = req['prior_approval_letter_doc_name'];
          this.prior_approval_letter_token = req['prior_approval_letter_doc_token'];

          if ( this.prior_approval_letter_name && this.prior_approval_letter_token) {
             // skip prior approval letter because it comes from backend
            // tslint:disable-next-line:radix
            this.reqcount = (req['count'] &&  parseInt( req['count'] )) ? parseInt( req['count'] ) - 100 : 0;
          } else {
            this.reqcount = req['count'];
          }
          this.member = req['memberdata'];

          this.company_reservation_at = req['company_reservation_at'];
          this.company_exp_at = req['company_exp_at'];

          let company_reservation_at_date = new Date(this.company_reservation_at);
          let company_reservation_at_date_time = company_reservation_at_date.getTime();
          let nameCreated = company_reservation_at_date_time - 24 * 60 * 60 * 1000;

          console.log(new Date(nameCreated).toString());
          this.min = new Date(nameCreated);

          let today  = new Date();
          let todayMilisecond = today.getTime();

          let company_exp_at_date = new Date(this.company_exp_at);
          let company_exp_at_date_time = company_exp_at_date.getTime();
          let nameExpired = (company_exp_at_date_time > todayMilisecond ) ? todayMilisecond : company_exp_at_date_time;

          console.log(new Date(nameExpired).toString());
          this.max = new Date(nameExpired);

          this.resolution_date_set = (req['resolution_date']) ? true : false;
          this.resolution_date = req['resolution_date'];

          this.oldRefId = req['old_company_id'];

          this.showandhide = true;
          this.spinner.hide();
          this.getOtherDocs();
        },
        error => {
          this.spinner.hide();
        }
      );
  }

  setResoultionDate() {
    var data: any = {
      email: this.auth.getEmail(),
      dirId: this.selectCat,
      oldRefid: this.oldRefId,
      newRefid: this.newRefid,
      resolutiondate: this.resolution_date
    };
    this.spinner.show();

    // load Company data from the server
    this.nameChange.getChangeNameFormFill(data)
      .subscribe(
        req => {
          this.helper.download(req, '', 'form3.pdf');
          this.getDocumentfield();
        }
      );
  }

  getResubmitData(comid: string) {
    this.spinner.show();
    this.showandhide = false;
    this.reqDocument = [];
    this.request_status = 'COMPANY_NAME_CHANGE_REQUEST_TO_RESUBMIT';
    this.rec.getNameReservationData(Number(comid))
      .subscribe(
        req => {
          this.reqDocument = req['companyResubmitedDoc'];
          this.otherDocsForResubmit = req['otherDocsForNameChangeResubmit'];
          this.comments = req['comments'];
          this.resolution_date_set = true;
          this.items = this.reqDocument.length * 100;
          this.showandhide = true;
         // this.spinner.hide();
          this.fileToken = [];
          this.reqDocument.forEach(element => {
            if (element.file_token !== null) {
              this.percentDone[element.id] = 100;
              this.uploadCountAndMulti += 100;
            }
          });
          this.getOtherDocs();
        },
        error => {
          this.spinner.hide();
        }
      );
  }

  upload(files: File[], id: string, required: boolean) {
    if (files[0].size > 4194304) {
      this.spinner.hide();
      swal({
        type: 'error',
        title: 'Sorry',
        text: 'File size must 4mb or below!',
      });
      return;
    } else if (files[0].type !== 'application/pdf') {
      this.spinner.hide();
      swal({
        type: 'error',
        title: 'Sorry',
        text: 'Only pdf can be uploaded!',
      });
      return;
    }
    if (required) {
      this.uploadCount += 100;
    }
    this.fileName[id] = files[0].name;
    this.uploadAndProgress(files, id);
    this.id += 1;
  }


  uploadOtherDoc(event, fileNane, fileDBID  ) {


    // tslint:disable-next-line:prefer-const
    let fileList: FileList = event.target.files;
    if (fileList.length > 0) {
      this.spinner.show();

     for (let i = 0; i < fileList.length; i++ ) {

      // tslint:disable-next-line:prefer-const
      let file: File = fileList[i];

     // console.log(fileList[0]);

      // tslint:disable-next-line:prefer-const
      let fileSize = fileList[i].size;

      if (fileSize > 1024 * 1024 * 4) { // 4mb restriction
        alert('File size should be less than 4 MB');
        return false;
      }

      // tslint:disable-next-line:prefer-const
      let formData: FormData = new FormData();
      formData.append('uploadFile', file, file.name);
      formData.append('fileName', this.slugify(fileNane));

      let filename = (this.other_doc_name) ?  this.other_doc_name + '.pdf' : file.name;
      formData.append('fileRealName', filename );
      formData.append('fileTypeId', fileDBID);
      formData.append('fileDescription', this.other_doc_name);
      formData.append('company_id', this.newRefid );
      // tslint:disable-next-line:prefer-const
      let headers = new HttpHeaders();
      headers.append('Content-Type', 'multipart/form-data');
      headers.append('Accept', 'application/json');

      // tslint:disable-next-line:prefer-const
      let uploadurl = this.url.getuploadOtherFileAPI();

      this.http.post(uploadurl, formData, { headers: headers })
        .subscribe(
          (data: any) => {
            this.other_doc_name = '';
            this.uploadbtnOther.nativeElement.value = '';
            this.getOtherDocs();
            // this.spinner.hide();
          },
          error => {
            console.log(error);
            this.spinner.hide();
          }
        );



        }


    }

  }


  uploadOtherResumittedDoc(event, multiple_id  ) {


    // tslint:disable-next-line:prefer-const
    let fileList: FileList = event.target.files;
    if (fileList.length > 0) {
      this.spinner.show();

     for (let i = 0; i < fileList.length; i++ ) {

      // tslint:disable-next-line:prefer-const
      let file: File = fileList[i];

     // console.log(fileList[0]);

      // tslint:disable-next-line:prefer-const
      let fileSize = fileList[i].size;

      if (fileSize >= 1024 * 1024 * 4) { // 4mb restriction
        alert('You can upload document only up to 4 MB');
        this.spinner.hide();
        return false;
      }

      // tslint:disable-next-line:prefer-const
      let formData: FormData = new FormData();
      formData.append('uploadFile', file, file.name);
      formData.append('multiple_id', multiple_id);
      formData.append('company_id', this.newRefid );
      // tslint:disable-next-line:prefer-const
      let headers = new HttpHeaders();
      headers.append('Content-Type', 'multipart/form-data');
      headers.append('Accept', 'application/json');

      // tslint:disable-next-line:prefer-const
      let uploadurl = this.url.getuploadOtherResubmitFileAPI();

      this.http.post(uploadurl, formData, { headers: headers })
        .subscribe(
          (data: any) => {
            this.other_doc_name = '';
            this.getOtherDocs();
            // this.spinner.hide();
          },
          error => {
            console.log(error);
            this.spinner.hide();
          }
        );


        }


    }

  }

  removeDoc(token){
    const data = {
      file_token: token ,
    };
    this.spinner.show();

    // load Company data from the server
    this.general.removeNameChangeOtherDoc(data)
      .subscribe(
        req => {
          this.uploadbtnOther.nativeElement.value = '';
          this.getOtherDocs();
        }
      );

  }

  private slugify(text) {
    return text.toString().toLowerCase()
      .replace(/\s+/g, '-')           // Replace spaces with -
      .replace(/[^\w\-]+/g, '')       // Remove all non-word chars
      .replace(/\-\-+/g, '-')         // Replace multiple - with single -
      .replace(/^-+/, '')             // Trim - from start of text
      .replace(/-+$/, '');            // Trim - from end of text
  }



  uploadAndProgress(files: File[], id: string) {
    this.spinner.show();
    const formData = new FormData();
    Array.from(files).forEach(f => {
      if (this.formdataservice.getResubmit) {
        formData.append('file', f);
        formData.append('id', id);
        formData.append('companyId', this.newRefid);
        formData.append('fileResubmit', 'true');
        formData.append('isNameChange', 'yes');
      } else {
        formData.append('file', f);
        formData.append('companyId', this.newRefid);
        formData.append('docId', id);
        formData.append('fileName', f.name);
        formData.append('isNameChange', 'yes');
      }

    });
    this.http.post(this.docService.url.setfileUploadAPI(), formData, { reportProgress: true, observe: 'events' }).subscribe(
      event => {
        if (event.type === HttpEventType.UploadProgress) {
          this.percentDone[id] = 50;  //   Math.round(100 * event.loaded / event.total);
        } else if (event instanceof HttpResponse) {
          this.uploadSuccess = true;
          this.percentDone[id] = 100;
          this.uploadCountAndMulti += 100;
          this.fileToken[id] = event['body']['key'];
          this.getOtherDocs();
          this.validateCourtSection();
          this.spinner.hide();
        }
      },
      error => {
        this.validateCourtSection();
        this.spinner.hide();
        this.percentDone[id] = 0;
      }
    );
  }

  onSubmit() {

    var divDate = 0;

    if (this.dayCount(this.resDate) >= this.MaxDate) {
      divDate = this.dayCount(this.resDate) / this.MaxDate;
      this.item = [
        {
          fee_type: 'PAYMENT_NAME_RESERVATION',
          description: 'For approval of a name of a company (Name Change)',
          quantity: 1,
        },
        {
          fee_type: 'PAYMENT_NAME_CHANGE_PRIOR_APPROVAL_LETTER',
          description: 'Name change prior approval letter',
          quantity: 1,
        },
        {
          fee_type: 'PAYMENT_PENALTY_LIMITED_DATE',
          description: 'Penalty',
          quantity: divDate,
        }
      ];
    } else {
      this.item = [
        {
          fee_type: 'PAYMENT_NAME_RESERVATION',
          description: 'For approval of a name of a company (Name Change)',
          quantity: 1,
        },
        {
          fee_type: 'PAYMENT_NAME_CHANGE_PRIOR_APPROVAL_LETTER',
          description: 'Name change prior approval letter',
          quantity: 1,
        }
      ];
    }

    swal({
      title: 'Are you sure?',
      text: 'You wont be able to revert back',
      type: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#3085d6',
      cancelButtonColor: '#d33',
      confirmButtonText: 'Yes, Continue'
    }).then((result) => {
      if (result.value) {
        this.iBy.setItem(this.item);
        this.iBy.setModuleType('MODULE_NAME_CHANGE');
        this.iBy.setModuleId(this.Idcn);
        this.iBy.setDescription('Name Change');
        this.iBy.setExtraPayment(null);

        this.updateCourtOrderDetails();
      }
    });
  }


  validateCourtSection(){


      if (this.court_status === 'yes') {

        this.validateCourtSectionFlag = ( this.court_name  && this.court_case_no && this.court_date && ( this.court_penalty ?  parseFloat(this.court_penalty) >= 0  : true)  );
        return true;
      }
      if ( this.court_status === 'no') {
        this.validateCourtSectionFlag =  true;
        return true;
      }
      console.log('3333');
      this.validateCourtSectionFlag =  false;


  }

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

  // tslint:disable-next-line:use-life-cycle-interface
  ngOnDestroyfiles(token: string, id: string, required: boolean): void {
    swal({
      title: 'Are you sure?',
      text: 'Are you sure you want to delete ?',
      type: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#3085d6',
      cancelButtonColor: '#d33',
      confirmButtonText: 'Yes, Continue'
    }).then((result) => {
      if (result.value) {
        this.spinner.show();
        this.percentDone[id] = 0;
        this.general.onDestroytoServer(token)
          .subscribe(
            response => {
              if (response === true) {
                this.percentDone[id] = 0;
                if (required) {
                  this.uploadCount -= 100;
                }
                this.validateCourtSection();
                this.spinner.hide();

                swal(
                  'Deleted!',
                  'Your file has been deleted.',
                  'success'
                );
              } else {
                this.percentDone[id] = 100;
              }
            }
          );
      }
    });
  }

  onReSubmit() {
    this.spinner.show();
    const reSubmit: any = {
      refId: this.Idcn,
    };

    this.nameChange.setReSubmitedData(reSubmit)
      .subscribe(
        req => {
          this.spinner.hide();
          this.router.navigate(['/dashboard/home']);
          this.updateCourtOrderDetails();
          this.snotifyService.success('Re-Submit update Successful', 'Success');
        },
        error => {
          this.spinner.hide();
          this.snotifyService.error('Re-Submit update un-successful!', 'error');
        }
      );
  }



  ngOnFileDelete(token: string, id: string, event): void {
    swal({
      title: 'Are you sure?',
      text: 'Are you sure you want to delete ?',
      type: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#3085d6',
      cancelButtonColor: '#d33',
      confirmButtonText: 'Yes, Continue'
    }).then((result) => {
      if (result.value) {
        this.spinner.show();
        this.percentDone[id] = 100;
        this.general.onReSubmitDestroytoServer(token)
          .subscribe(
            response => {
              if (response === true) {
                this.percentDone[id] = 50;
                this.fileToken[id] = 0;
                this.uploadCountAndMulti -= 100;
                this.fileName[id] = '';
                swal(
                  'Deleted!',
                  'Your file has been deleted.',
                  'success'
                );
                this.getResubmitData(this.newRefid);
              }
              this.percentDone[id] = 0;
              this.spinner.hide();
            }
          );
      }
    });
  }

  dayCount(date): number {
    var diff = Math.abs(new Date(date).getTime() - new Date().getTime());
    return Math.ceil(diff / (1000 * 3600 * 24));
  }

  formatDate(date) {
    var d = new Date(date);
    var month = '' + (d.getMonth() + 1);
    var day = '' + d.getDate();
    var year = d.getFullYear();

    if (month.length < 2) {
      month = '0' + month.toString();
    }
    if (day.length < 2) {
      day = '0' + day.toString();
    }

    return [year, month, day].join('-');
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
