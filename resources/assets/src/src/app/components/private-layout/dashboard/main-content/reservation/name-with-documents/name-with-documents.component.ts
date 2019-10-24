import { IBuyDetails } from './../../../../../../storage/ibuy-details';
import { HelperService } from './../../../../../../http/shared/helper.service';
import { NgxSpinnerService } from 'ngx-spinner';
import { Component, OnInit, ElementRef, ViewChild } from '@angular/core';
import { Router } from '@angular/router';
import { GeneralService } from '../../../../../../http/services/general.service';
import { ReservationComponent } from '../reservation.component';
import { DocumentsService } from '../../../../../../http/services/documents.service';
import { AuthService } from '../../../../../../http/shared/auth.service';
import { IDocGroup } from '../../../../../../http/models/doc.model';
import { HttpEventType, HttpClient, HttpResponse, HttpHeaders } from '@angular/common/http';
import swal from 'sweetalert2';
import { Item } from 'src/app/http/models/payment';
import { IUploadOtherDocs } from '../../../../../../http/models/recervationdata.model';
import { APIConnection } from '../../../../../../http/services/connections/APIConnection';

@Component ({
  selector: 'app-name-with-documents',
  templateUrl: './name-with-documents.component.html',
  styleUrls: ['./name-with-documents.component.scss']
})
export class NameWithDocumentsComponent implements OnInit {

  url: APIConnection = new APIConnection();

  @ViewChild('form') FileInputVariable: ElementRef;
  @ViewChild('uploadbtnOther')
  uploadbtnOther: ElementRef;
  public docs: IDocGroup;
  percentDone: Array<number> = [];
  uploadSuccess: boolean;
  fileName: Array<string> = [];
  public id: number;
  public i = 1;
  public fileToken: Array<number> = [];
  public reqcount = 0;
  public uploadCount = 0;

  otherUploadedList: IUploadOtherDocs = { docs: [] };
  otherFileDBID: string = null;
  other_doc_name = '';

  constructor(
    private helper: HelperService,
    private route: Router,
    private http: HttpClient,
    private general: GeneralService,
    public res: ReservationComponent,
    private spinner: NgxSpinnerService,
    private docService: DocumentsService,
    private auth: AuthService,
    private iBy: IBuyDetails
    ) {

    }

  ngOnInit() {
    this.getDocumentfield();
  }

  getDocumentfield(): void {
    let hasOldCompanyNumber = localStorage.getItem('hasOldNumber');
    let setCompanyId = ( hasOldCompanyNumber === 'yes') ? this.auth.getCompanyId() : null;
    this.general.getDocFeild(this.res.companyType, 'NAME_REG', setCompanyId)
      .subscribe(
        req => {
          this.docs = req['collection'];
          this.reqcount = req['count'];

          this.getOtherDocs();
        }
      );
  }

  private getOtherDocs() {

    const data = {
      company_id: (localStorage.getItem('hasOldNumber')) ? this.auth.getCompanyId() : localStorage.getItem('ID'),
      type: this.res.companyType
    };
    this.spinner.show();

    // load Company data from the server
    this.general.getNameChangeOtherDocListForName(data)
      .subscribe(
        req => {
          console.log(req);
          this.spinner.hide();
          this.otherUploadedList.docs = req['docs'];
          this.otherFileDBID = req['dbid'];
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


  uploadOtherDoc(event, fileNane ) {


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
      formData.append('fileTypeId', this.otherFileDBID);
      formData.append('fileDescription', this.other_doc_name);
      let companyId = (localStorage.getItem('hasOldNumber')) ? this.auth.getCompanyId() : localStorage.getItem('ID');
      formData.append('company_id', companyId);
      // tslint:disable-next-line:prefer-const
      let headers = new HttpHeaders();
      headers.append('Content-Type', 'multipart/form-data');
      headers.append('Accept', 'application/json');

      // tslint:disable-next-line:prefer-const
      let uploadurl = this.url.getuploadOtherFileForNameAPI();

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

  removeOtherDoc(token){
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
      formData.append('file', f);
      formData.append('companyId', this.auth.getCompanyId());
      formData.append('docId', id);
      formData.append('fileName', f.name);
      formData.append('isNameChange', 'no');
    });
    this.http.post(this.docService.url.setfileUploadAPI(), formData, { reportProgress: true, observe: 'events' }).subscribe(
      event => {
        if (event.type === HttpEventType.UploadProgress) {
          this.percentDone[id] = 50;  //   Math.round(100 * event.loaded / event.total);
        } else if (event instanceof HttpResponse) {
          this.uploadSuccess = true;
          this.percentDone[id] = 100;
          this.fileToken[id] = event['body']['key'];
          this.spinner.hide();
        }
      },
      error => {
        this.spinner.hide();
        this.percentDone[id] = 0;
      }
    );
  }

  onSubmit() {
    const item: Array<Item> = [{
      fee_type: 'PAYMENT_NAME_RESERVATION',
      description: 'For approval of a name of a company (Name Reservation)',
      quantity: 1,
    }];

    swal({
      title: 'Are you sure?',
      text: 'You won\'t be able to revert this!',
      type: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#3085d6',
      cancelButtonColor: '#d33',
      confirmButtonText: 'Yes, Continue'
    }).then((result) => {
      if (result.value) {
        this.iBy.setItem(item);
        this.iBy.setModuleType('MODULE_NAME_RESERVATION');
        this.iBy.setModuleId(this.auth.getCompanyId());
        this.iBy.setDescription('Name Reservation');
        this.iBy.setExtraPayment(null);
        this.route.navigate(['reservation/payment']);
      }
    });
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
      text: 'You want to delete ?',
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
}
