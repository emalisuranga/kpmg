import { GlobleUserService } from '../../../../../../http/shared/globle.user.service';
import { DataService } from './../../../../../../storage/data.service';
import { Component, OnInit, HostListener } from '@angular/core';
import { Validators, FormBuilder, FormGroup } from '@angular/forms';
import { Router } from '@angular/router';
import { ReservationComponent } from '../reservation.component';
import { NameResarvationService } from '../../../../../../http/services/name-resarvation.service';
import { ToastrService } from 'ngx-toastr';
import { DatePicker } from 'angular2-datetimepicker';
declare var google: any;
@Component({
  selector: 'app-name-with-agree',
  templateUrl: './name-with-agree.component.html',
  styleUrls: ['./name-with-agree.component.scss']
})
export class NameWithAgreeComponent implements OnInit {

  nameResForm: FormGroup;
  tamilControl = null;
  sinhalaControl = null;
  tamilelements;
  sinElements;
  ResNumber = false;
  public date: Date = new Date();
  public min = new Date(2018, 1, 12, 10, 30);

  public max = new Date(this.date.getUTCFullYear(), this.date.getUTCMonth(), this.date.getUTCDate(), this.date.getUTCHours(), this.date.getUTCMinutes());


  constructor(
    private formBuilder: FormBuilder,
    public route: Router,
    private data: DataService,
    private user: GlobleUserService,
    public res: ReservationComponent,
    public resvation: NameResarvationService,
    private snotifyService: ToastrService
  ) { }

  ngOnInit() {
    this.tamilelements = document.getElementsByClassName('tamilname');
    this.sinElements = document.getElementsByClassName('sinhalaName');
    if (this.res.oldnumber.toString().length > 0) {
      this.nameResForm = this.formBuilder.group({
        sinhalaName: [this.sinhalaControl],
        tamilname: [this.tamilControl],
        abreviations: [null],
        agreeCheck: [null, Validators.required]
      });
    } else {
      this.nameResForm = this.formBuilder.group({
        sinhalaName: [this.sinhalaControl],
        tamilname: [this.tamilControl],
        abreviations: [null],
        agreeCheck: [null, Validators.required]
      });
    }
  }

  get getControler() { return this.nameResForm.controls; }

  onSubmit() {
    if (this.getControler.invalid) {
      return;
    }

    const nameReceive: any = {
      email: this.user.getEmail,
      typeId: this.res.companyType,
      englishName: this.res.name,
      sinhalaName: this.sinhalaControl,
      tamilname: this.tamilControl,
      postfix: this.res.postfixname,
      abreviations: this.getControler.abreviations.value,
      oldnumber: this.res.oldnumber
    };

    this.resvation.setNameReceive(nameReceive)
      .subscribe(
        req => {
          window.history.forward();
          this.data.setLocaldata('ID', req['company']);

          localStorage.removeItem('hasOldNumber');
          if (req['is_from_name_change']) {
            localStorage.setItem('hasOldNumber', 'yes');
          }

          this.route.navigate(['reservation/documents']);
        },
        error => {
          this.route.navigate(['/']);
          this.snotifyService.error(this.res.name + ' ' + this.res.postfixname + ' name reserved Unsuccessfully!', 'Error');
        }
      );
  }

  onLoadSinhala() {
    this.loadsinhala();
  }

  onLoadTamil() {
    this.loadTamil();
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
        this.sinhalaControl = this.sinElements[0].value;
        this.tamilControl = this.tamilelements[0].value;
      },
        1000);
    }
  }

}
