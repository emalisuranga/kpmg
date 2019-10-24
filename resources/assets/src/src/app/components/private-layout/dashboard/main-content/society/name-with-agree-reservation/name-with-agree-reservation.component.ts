import { GlobleUserService } from '../../../../../../http/shared/globle.user.service';
import { Component, OnInit, HostListener } from '@angular/core';
import { Validators, FormBuilder, FormGroup } from '@angular/forms';
import { Router, ActivatedRoute } from '@angular/router';
import { UserService } from '../../../../../../http/services/user.service';
import { DataService } from '../../../../../../storage/data.service';
declare var google: any;

@Component({
  selector: 'app-name-with-agree-reservation',
  templateUrl: './name-with-agree-reservation.component.html',
  styleUrls: ['./name-with-agree-reservation.component.scss']
})
export class NameWithAgreeReservationComponent implements OnInit {
  name: string;
  needApproval: boolean;
  applicantName: string;
  nameResForm: FormGroup;
  tamilelements;
  sinElements;
  tamilControl = null;
  sinhalaControl = null;

  adtamilelements;
  adsinElements;
  adtamilControl = null;
  adsinhalaControl = null;

  constructor(
    private formBuilder: FormBuilder,
    private route: Router,
    private user: UserService,
    private data: DataService) { }

  ngOnInit() {

    if (typeof this.data.storage === 'undefined') {
      this.route.navigate(['/']);
    }
    this.name = this.data.storage['name'];
    this.needApproval = this.data.storage['needApproval'];

    this.nameResForm = this.formBuilder.group({
      sinhalaName: [this.sinhalaControl],
      tamilname: [this.tamilControl],
      adsinhalaName: [this.adsinhalaControl],
      adtamilname: [this.adtamilControl],
      address: [null, Validators.required],
      abreviations: [null],
      agreeCheck: [null, Validators.required]
    });
    this.tamilelements = document.getElementsByClassName('tamilname');
    this.sinElements = document.getElementsByClassName('sinhalaName');
    this.adtamilelements = document.getElementsByClassName('adtamilname');
    this.adsinElements = document.getElementsByClassName('adsinhalaName');
  }



  get getControler() { return this.nameResForm.controls; }


  onSubmit() {

    this.data.storage1 = {
      name: this.name.toUpperCase(),
      sinhalaName: this.sinhalaControl,
      tamilname: this.tamilControl,
      adsinhalaName: this.adsinhalaControl,
      adtamilname: this.adtamilControl,
      address: this.getControler.address.value,
      abreviations: this.getControler.abreviations.value,
      needApproval: this.needApproval
    };
    this.route.navigate(['/dashboard/societyincorporation']);
  }

  onLoadSinhala() {
    this.loadsinhala();
  }

  onLoadTamil() {
    this.loadTamil();
  }

  onLoadadSinhala() {
    this.loadadsinhala();
  }

  onLoadadTamil() {
    this.loadadTamil();
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

  loadadsinhala() {
    const sinhalOptions = {
      sourceLanguage: google.elements.transliteration.LanguageCode.ENGLISH,
      destinationLanguage: [google.elements.transliteration.LanguageCode.SINHALESE],
      shortcutKey: 'ctrl+s',
      transliterationEnabled: true
    };
    const TradSinhalaControl = new google.elements.transliteration.TransliterationControl(sinhalOptions);
    TradSinhalaControl.makeTransliteratable(this.adsinElements);
  }

  loadadTamil() {
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


