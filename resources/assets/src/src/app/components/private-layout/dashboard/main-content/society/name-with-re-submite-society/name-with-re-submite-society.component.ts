import swal from 'sweetalert2';
import { ICompanyCommentWith } from '../../../../../../http/models/recervationdata.model';
import { HttpEventType, HttpResponse, HttpClient } from '@angular/common/http';
import { DocumentsService } from '../../../../../../http/services/documents.service';
import { IReqDocument } from '../../../../../../http/models/file.model';
import { ToastrService } from 'ngx-toastr';
import { INames, IReSubmit1 } from '../../../../../../http/models/recervationdata.model';
import { NameResarvationService } from '../../../../../../http/services/name-resarvation.service';
import { ActivatedRoute, Router } from '@angular/router';
import { Component, OnInit, OnDestroy, Output, EventEmitter, Input, HostListener } from '@angular/core';
import { FormBuilder, FormGroup, FormControl, Validators, FormArray } from '@angular/forms';
import { GeneralService } from '../../../../../../http/services/general.service';
import { NgxSpinnerService } from 'ngx-spinner';
import { HelperService } from '../../../../../../http/shared/helper.service';
import { SocietyDataService } from '../society-data.service';
import { DataService } from '../../../../../../storage/data.service';
import { SocietyService } from '../../../../../../http/services/society.service';
declare var google: any;

@Component({
  selector: 'app-name-with-re-submite-society',
  templateUrl: './name-with-re-submite-society.component.html',
  styleUrls: ['./name-with-re-submite-society.component.scss']
})
export class NameWithReSubmiteSocietyComponent implements OnInit {
  id: number;
  private sub: any;
  public name: INames;
  public comments: Array<ICompanyCommentWith> = [];
  public formGroup: FormGroup;
  public reqDocument: Array<IReqDocument>;
  public percentDone: Array<number> = [];
  public fileToken: Array<number> = [];
  public socId: number;
  public items = 0;
  public uploadCountAndMulti = 0;
  needApproval: boolean;
  tamilControl = null;
  sinhalaControl = null;
  tamilelements;
  sinElements;

  adtamilelements;
  adsinElements;
  adtamilControl = null;
  adsinhalaControl = null;

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
    private snotifyService: ToastrService,
    private SocData: SocietyDataService,
    private societyService: SocietyService,
    public data: DataService) { }

  ngOnInit() {
    this.spinner.show();
    this.createGroupBuild();

    this.data.storage1 = JSON.parse(localStorage.getItem('storage1'));
    this.getcompanyName.setValue(this.data.storage1['name']);
    this.getName.setValue(this.data.storage1['name']);
    this.getSinhalaName.setValue(this.data.storage1['sinhalaName']);
    this.sinhalaControl = this.data.storage1['sinhalaName'];
    this.getTamilName.setValue(this.data.storage1['tamilname']);
    this.tamilControl = this.data.storage1['tamilname'];
    this.getadSinhalaName.setValue(this.data.storage1['adsinhalaName']);
    this.adsinhalaControl = this.data.storage1['adsinhalaName'];
    this.getadTamilName.setValue(this.data.storage1['adtamilname']);
    this.adtamilControl = this.data.storage1['adtamilname'];
    this.getAbbreviationName.setValue(this.data.storage1['abreviations']);
    this.getAddress.setValue(this.data.storage1['address']);
    this.needApproval = this.data.storage1['needApproval'];
    this.socId = this.data.storage1['socId'];
    this.loadsocietyComments();
    this.spinner.hide();

    this.tamilelements = document.getElementsByClassName('tamilname');
    this.sinElements = document.getElementsByClassName('sinhalaName');
    this.adtamilelements = document.getElementsByClassName('adtamilName');
    this.adsinElements = document.getElementsByClassName('adsinhalaName');

  }

  createGroupBuild() {
    this.formGroup = this.formBuilder.group({
      companyName: new FormControl(null),
      name: new FormControl(null, Validators.required),
      sinhalaName: new FormControl(this.sinhalaControl),
      tamilName: new FormControl(this.tamilControl),
      adsinhalaName: new FormControl(this.adsinhalaControl),
      adtamilName: new FormControl(this.adtamilControl),
      address: new FormControl(null, Validators.required),
      abbreviations: new FormControl(null),
      approval: new FormControl(null)
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

  get getapproval() { return this.formGroup.get('approval'); }

  get getName() { return this.formGroup.get('name'); }

  get getSinhalaName() { return this.formGroup.get('sinhalaName'); }

  get getTamilName() { return this.formGroup.get('tamilName'); }

  get getAbbreviationName() { return this.formGroup.get('abbreviations'); }

  get getadSinhalaName() { return this.formGroup.get('adsinhalaName'); }

  get getadTamilName() { return this.formGroup.get('adtamilName'); }

  get getAddress() { return this.formGroup.get('address'); }

  onSubmit(): void {
    this.spinner.show();
    const reSubmit: IReSubmit1 = {
      refId: this.socId,
      companyName: this.getName.value,
      sinhalaName: this.sinhalaControl,
      tamileName: this.tamilControl,
      abbreviation_desc: this.getAbbreviationName.value,
      adsinhalaName: this.adsinhalaControl,
      adtamileName: this.adtamilControl,
      address: this.getAddress.value,
      needApproval : this.needApproval
    };
    localStorage.setItem('reSubmit', JSON.stringify(reSubmit));
    this.router.navigate(['/dashboard/societyresubmitincorporation']);

    // this.reservationService.setReSubmitedData(reSubmit)
    //   .subscribe(
    //     req => {
    //       this.spinner.hide();
    //       this.router.navigate(['/dashboard/societyresubmitincorporation']);
    //       this.snotifyService.success('Re-Submit update Successful', 'Success');
    //     },
    //     error => {
    //       this.spinner.hide();
    //       this.snotifyService.error('Re-Submit update un-successful!', 'error');
    //     }
    //   );
  }


  ngBind(event): void {
    this.getcompanyName.setValue(event.name.toUpperCase());
    this.getName.setValue(event.name.toUpperCase());
    this.getapproval.setValue(event.approval);
    this.needApproval = this.getapproval.value;
  }

  loadsocietyComments() {
    const data = {
      id: this.data.storage1['socId'],
    };
    this.societyService.societyComments(data)
      .subscribe(
        req => {
          if (req['data']) {
            if (req['data']['comments']) {
              this.comments = req['data']['comments'];

            }

          }
          else{
          }
        },
        error => {
          console.log(error);
        }
      );
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

  onLoadadsinhala() {
    const sinhalOptions = {
      sourceLanguage: google.elements.transliteration.LanguageCode.ENGLISH,
      destinationLanguage: [google.elements.transliteration.LanguageCode.SINHALESE],
      shortcutKey: 'ctrl+s',
      transliterationEnabled: true
    };
    const TradSinhalaControl = new google.elements.transliteration.TransliterationControl(sinhalOptions);
    TradSinhalaControl.makeTransliteratable(this.adsinElements);
  }

  onLoadadTamil() {
    const tamilOptions = {
      sourceLanguage: google.elements.transliteration.LanguageCode.ENGLISH,
      destinationLanguage: [google.elements.transliteration.LanguageCode.TAMIL],
      shortcutKey: 'ctrl+s',
      transliterationEnabled: true
    };
    const TradtamilControl = new google.elements.transliteration.TransliterationControl(tamilOptions);
    TradtamilControl.makeTransliteratable(this.adtamilelements);
  }

  @HostListener('keydown', ['$event']) onKeyDown(e) {
    if ( e.keyCode === 32 || e.keyCode === 13 || e.keyCode === 46 || e.keyCode === 8) {
      setTimeout(() =>
      {
        this.sinhalaControl = this.sinElements[0].value;
        this.tamilControl = this.tamilelements[0].value;
        this.adsinhalaControl = this.adsinElements[0].value;
        this.adtamilControl = this.adtamilelements[0].value;
      },
      1000);
    }
  }

}
