import swal from 'sweetalert2';
import { ICompanyCommentWith } from './../../../../../../http/models/recervationdata.model';
import { HttpEventType, HttpResponse, HttpClient } from '@angular/common/http';
import { DocumentsService } from './../../../../../../http/services/documents.service';
import { IReqDocument } from './../../../../../../http/models/file.model';
import { ToastrService } from 'ngx-toastr';
import { INames, IReSubmit } from '../../../../../../http/models/recervationdata.model';
import { NameResarvationService } from '../../../../../../http/services/name-resarvation.service';
import { ActivatedRoute, Router } from '@angular/router';
import { Component, OnInit, OnDestroy, Output, EventEmitter, Input, HostListener } from '@angular/core';
import { FormBuilder, FormGroup, FormControl, Validators, FormArray } from '@angular/forms';
import { GeneralService } from '../../../../../../http/services/general.service';
import { NgxSpinnerService } from '../../../../../../../../node_modules/ngx-spinner';
import { HelperService } from '../../../../../../http/shared/helper.service';
declare var google: any;

@Component ({
  selector: 'app-name-with-re-submited',
  templateUrl: './name-with-re-submited.component.html',
  styleUrls: ['./name-with-re-submited.component.scss']
})
export class NameWithReSubmitedComponent implements OnInit, OnDestroy {
  id: number;
  private sub: any;
  public name: INames;
  public comments: Array<ICompanyCommentWith> = [];
  public formGroup: FormGroup;
  public reqDocument: Array<IReqDocument>;
  public percentDone: Array<number> = [];
  public fileToken: Array<number> = [];
  public comanyId: string;
  public items = 0;
  public uploadCountAndMulti = 0;

  tamilControl = null;
  sinhalaControl = null;
  tamilelements;
  sinElements;

  fileName: Array<string> = [];

  @Input() exCompanyType: string;
  @Input() expostFix: string;

  constructor(
    private formBuilder: FormBuilder,
    private router: Router,
    private http: HttpClient,
    private docService: DocumentsService,
    private helper: HelperService,
    private route: ActivatedRoute,
    private general: GeneralService,
    private spinner: NgxSpinnerService,
    private reservationService: NameResarvationService,
    private snotifyService: ToastrService) { }

  ngOnInit() {
    this.spinner.show();
    this.createGroupBuild();
    this.sub = this.route.params.subscribe(params => {
      this.reservationService.getNameReservationData(params['id'])
        .subscribe(
          req => {
            this.name = req['companyInfor'];
            this.reqDocument = req['companyResubmitedDoc'];
            this.fileToken = [];
            this.reqDocument.forEach((key: any, val: any)  =>  {
              this.fileToken[key.id] = key.file_token;
              if (key.file_token !== null){
                this.percentDone[key.id] = 100;
                this.uploadCountAndMulti += 100;
              }
            });
            this.id = params['id'];
            this.comments = req['comments'];
            this.getcompanyName.setValue(this.name.name.toUpperCase() + ' ' + this.name.postfix);
            this.getName.setValue(this.name.name.toUpperCase());
            this.getSinhalaName.setValue(this.name.name_si);
            this.sinhalaControl = this.name.name_si;
            this.getTamilName.setValue(this.name.name_ta);
            this.tamilControl = this.name.name_ta;
            this.getAbbreviationName.setValue(this.name.abbreviation_desc);
            this.comanyId = this.name.id;
            this.exCompanyType = this.name.type_id;
            this.expostFix = this.name.postfix;
            this.spinner.hide();
            this.items = this.reqDocument.length * 100;
          }
        );
    });

    this.tamilelements = document.getElementsByClassName('tamilname');
    this.sinElements = document.getElementsByClassName('sinhalaName');
  }

  createGroupBuild() {
    this.formGroup = this.formBuilder.group({
      companyName: new FormControl(null),
      name: new FormControl(null, Validators.required),
      sinhalaName: new FormControl(this.sinhalaControl),
      tamilName: new FormControl(this.tamilControl),
      abbreviations: new FormControl(null)
    });
  }


  // deleteRow(index: number) {
  //   const control = <FormArray>this.formGroup.controls['items'];
  //   control.removeAt(index);
  // }

  // createItem(id: number = null, name: string = null, comments: string = null, file_token: string = null): FormGroup {
  //   return this.formBuilder.group({
  //     id: id,
  //     name: name,
  //     comments: comments,
  //     file_token: file_token,
  //     files: new FormControl(null, Validators.required)
  //   });
  // }

  get fitems(): FormArray {
    return this.formGroup.get('items') as FormArray;
  }

  get getcompanyName() { return this.formGroup.get('companyName'); }

  get getName() { return this.formGroup.get('name'); }

  get getSinhalaName() { return this.formGroup.get('sinhalaName'); }

  get getTamilName() { return this.formGroup.get('tamilName'); }

  get getAbbreviationName() { return this.formGroup.get('abbreviations'); }

  ngOnDestroy() {
    this.sub.unsubscribe();
  }

  onSubmit(): void {
    this.spinner.show();
    const reSubmit: IReSubmit = {
      refId: this.id,
      companyName: this.getName.value,
      sinhalaName: this.sinhalaControl,
      tamileName: this.tamilControl,
      abbreviation_desc: this.getAbbreviationName.value
    };

    this.reservationService.setReSubmitedData(reSubmit)
      .subscribe(
        req => {
          this.spinner.hide();
          this.router.navigate(['/dashboard/home']);
          this.snotifyService.success('Re-Submit update Successful', 'Success');
        },
        error => {
          this.spinner.hide();
          this.snotifyService.error('Re-Submit update un-successful!', 'error');
        }
      );
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

  ngBind(event): void {
    this.getcompanyName.setValue(event.name.toUpperCase() + ' ' + (event.postFix === null ? '' : event.postFix.toUpperCase()));
    this.getName.setValue(event.name.toUpperCase());
  }

  upload(files: File[], id: string) {
    if (files[0].size > 4194304) {
      swal({
        type: 'error',
        title: 'Sorry',
        text: 'File size must 4mb or below!',
      });
      return;
    } else if (files[0].type !== 'application/pdf') {
      swal({
        type: 'error',
        title: 'Sorry',
        text: 'Only pdf can be uploaded!',
      });
      return;
    }
    this.fileName[id] = files[0].name;
    this.uploadAndProgress(files, id);
  }

  uploadAndProgress(files: File[], id: string) {
    this.spinner.show();
    const formData = new FormData();
    Array.from(files).forEach(f => {
      formData.append('file', f);
      formData.append('id', id);
      formData.append('companyId', this.comanyId);
      formData.append('fileResubmit', 'true');
    });
    this.http.post(this.docService.url.setfileUploadAPI(), formData, { reportProgress: true, observe: 'events' }).subscribe(
      event => {
        if (event.type === HttpEventType.UploadProgress) {
          this.percentDone[id] = 50;  //   Math.round(100 * event.loaded / event.total);
        } else if (event instanceof HttpResponse) {
          this.percentDone[id] = 100;
          this.uploadCountAndMulti += 100;
          this.fileToken[id] = event['body']['key'];
          this.spinner.hide();
        }
      },
      error => {
        this.spinner.hide();
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
                this.fileToken[id] = null;
                this.uploadCountAndMulti -= 100;
                this.fileName[id] = '';
                swal(
                  'Deleted!',
                  'Your file has been deleted.',
                  'success'
                );
              }
              this.percentDone[id] = 0;
              this.spinner.hide();
            }
          );
      }
    });
  }

  onLoadSinhala() {
    const sinhalOptions = {
      sourceLanguage: google.elements.transliteration.LanguageCode.ENGLISH,
      destinationLanguage: [google.elements.transliteration.LanguageCode.SINHALESE],
      shortcutKey: 'ctrl+s',
      transliterationEnabled: true
    };
    const TrSinhalaControl = new google.elements.transliteration.TransliterationControl(sinhalOptions);
    TrSinhalaControl.makeTransliteratable(this.sinElements);
  }

  onLoadTamil() {
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
    if ( e.keyCode === 32 || e.keyCode === 13 || e.keyCode === 46 || e.keyCode === 8) {
      setTimeout(() =>
      {
        this.sinhalaControl = this.sinElements[0].value;
        this.tamilControl = this.tamilelements[0].value;
      },
      1000);
    }
  }

}
