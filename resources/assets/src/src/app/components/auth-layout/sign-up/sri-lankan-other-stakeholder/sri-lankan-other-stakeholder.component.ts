import { IAddress, IRegister } from './../../../../http/models/register.model';
import { DataService } from './../../../../storage/data.service';
import { ToastrService } from 'ngx-toastr';
import { Component, OnInit, ViewChild } from '@angular/core';
import { FormControl, Validators, FormGroup } from '@angular/forms';
import { ModalDirective } from 'angular-bootstrap-md';
import { Router } from '@angular/router';
import { NicValidator } from '../../../../directive/validator/compare.directive';
import { AppLoadService } from 'src/app/http/shared/app-load.service';
import { ICountry, IAdminCompanies, ISelectedAdminCompanies } from 'src/app/http/models/general.model';
import { NgxSpinnerService } from 'ngx-spinner';
import { GeneralService } from '../../../../http/services/general.service';


@Component({
  selector: 'app-sri-lankan-other-stakeholder',
  templateUrl: './sri-lankan-other-stakeholder.component.html',
  styleUrls: ['./sri-lankan-other-stakeholder.component.scss']
})
export class SriLankanOtherStakeholderComponent implements OnInit {
  @ViewChild('registerframe') modal: ModalDirective;

  public formGroup: FormGroup;
  pasportSection = false;
  foreignAddress = false;
  isLoginError: boolean;
  public rsData: Array<any> = [];
  public avaterPath: string;
  public fcountry: Array<ICountry> = [];
  public fcompanies: Array<IAdminCompanies>;
  // Default Avater
  imageUrl = 'assets/images/avatar/user-avatar.png';
  public fileToUpload: File;
  search: any;

  pvNumber: '';
  adminCompanies = [];
  currentCompanyList: Array<ISelectedAdminCompanies> = [];

  constructor(
    private router: Router,
    public data: DataService,
    private snotifyService: ToastrService,
    private AppLoad: AppLoadService,
    private spinner: NgxSpinnerService,
    private general: GeneralService,
  ) { }

  ngOnInit() {
    this.spinner.show();
    this.AppLoad.getCountry()
      .subscribe(
        req => {
          this.fcountry = req['countries'];
          this.fcompanies = req['companies'];
          this.spinner.hide();
        }
      );
    this.__validation();
  }
  getPro() {
    console.log(this.search);

  }
  __validation() {
    console.log('here');

    this.formGroup = new FormGroup({
      'title': new FormControl('', [
        Validators.required
      ]),
      'firstname': new FormControl('', [
        Validators.required,
        Validators.pattern('[a-zA-Z ]*')
      ]),
      'lastname': new FormControl('', [
        Validators.required,
        Validators.pattern('[a-zA-Z ]*')
      ]),
      'otherName': new FormControl(
        null,
        [Validators.pattern('[a-zA-Z ]*')]
      ),
      'avaterFile': new FormControl(
        null
      ),
      'nic': new FormControl(null, [
        Validators.required,
        NicValidator('nic')
      ]),
      'passportid': new FormControl(null, Validators.pattern('[0-9a-zA-Z]+')),
      'passportIssueCountry': new FormControl(null),
      'occupation': new FormControl(null),
      'mobileNumber': new FormControl(null, [Validators.required]),
      'telephoneNumber': new FormControl(null),
      'address01': new FormControl(null, Validators.required),
      'address02': new FormControl(null, Validators.required),
      'gndivision': new FormControl(null, Validators.required),
      'city': new FormControl(null, Validators.required),
      'district': new FormControl(null, Validators.required),
      'province': new FormControl(null, Validators.required),
      'country': new FormControl(null),
      'postCode': new FormControl(null, Validators.required),
      'frAddress01': new FormControl(null),
      'frAddress02': new FormControl(null),
      'frCity': new FormControl(null),
      'frProvince': new FormControl(null),
      'frCountry': new FormControl(null),
      'frPostCode': new FormControl(null),
   //   'adminAssignCompanies' :  new FormControl(null)
    });
    this.country.setValue('Sri Lanka');
  }

  get title() { return this.formGroup.get('title'); }
  get firstname() { return this.formGroup.get('firstname'); }
  get lastname() { return this.formGroup.get('lastname'); }
  get otherName() { return this.formGroup.get('otherName'); }
  get avaterFile() { return this.formGroup.get('avaterFile'); }
  get nic() { return this.formGroup.get('nic'); }
  get passportid() { return this.formGroup.get('passportid'); }
  get passportIssueCountry() { return this.formGroup.get('passportIssueCountry'); }
  get occupation() { return this.formGroup.get('occupation'); }
  get mobileNumber() { return this.formGroup.get('mobileNumber'); }
  get telephoneNumber() { return this.formGroup.get('telephoneNumber'); }
  get address01() { return this.formGroup.get('address01'); }
  get address02() { return this.formGroup.get('address02'); }
  get gndivision() { return this.formGroup.get('gndivision'); }
  get city() { return this.formGroup.get('city'); }
  get district() { return this.formGroup.get('district'); }
  get province() { return this.formGroup.get('province'); }
  get country() { return this.formGroup.get('country'); }
  get postCode() { return this.formGroup.get('postCode'); }
  get frAddress01() { return this.formGroup.get('frAddress01'); }
  get frAddress02() { return this.formGroup.get('frAddress02'); }
  get frCity() { return this.formGroup.get('frCity'); }
  get frProvince() { return this.formGroup.get('frProvince'); }
  get frCountry() { return this.formGroup.get('frCountry'); }
  get frPostCode() { return this.formGroup.get('frPostCode'); }
  get adminAssignCompanies() { return this.formGroup.get('adminAssignCompanies'); }

  showModal() {
    this.modal.show();
  }

  onSubmit(): void {

    if (!this.formGroup.valid) {
      return;
    }

    const registerData: IRegister = {
      title: this.title.value,
      firstname: this.firstname.value,
      lastname: this.lastname.value,
      otherName: this.otherName.value,
      avater: this.fileToUpload,
      nic: this.nic.value,
      passportid: this.passportid.value,
      passportIssueCountry: this.passportIssueCountry.value,
      occupation: this.occupation.value,
      mobileNumber: this.mobileNumber.value,
      telephoneNumber: this.telephoneNumber.value,
      isSrilanka: 'yes',
     // adminAssignCompanies: this.adminAssignCompanies.value,
      adminAssignCompanies: this.adminCompanies,
      isOtherStakeholder: true,
      isAdminUser: false

    };
    const addressData: Array<IAddress> = [
      {
        address01: this.address01.value,
        address02: this.address02.value,
        gndivision: this.gndivision.value,
        city: this.city.value.description_en,
        district: this.district.value.description_en,
        province: this.province.value.description_en,
        country: this.country.value,
        postCode: this.postCode.value
      },
      {
        address01: this.frAddress01.value,
        address02: this.frAddress02.value,
        city: this.frCity.value,
        district: '',
        province: this.frProvince.value,
        country: this.frCountry.value,
        postCode: this.frPostCode.value
      }
    ];

    this.data.regData = {
      details: registerData,
      address: addressData
    };

    this.router.navigate(['credential']);
  }

  onFileRemove() {
    this.imageUrl = 'assets/images/avatar/user-avatar.png';
    this.fileToUpload = null;
  }

  onFileInput(fileInput: any, ): void {
    if (fileInput.target.files && fileInput.target.files[0]) {
      const reader = new FileReader();
      this.fileToUpload = fileInput.target.files[0];
      reader.onload = (event: any) => {
        this.imageUrl = event.target.result;
      };
      reader.readAsDataURL(fileInput.target.files[0]);
    }
  }

  addAdminCompanies() {
    if (!this.pvNumber) {
        return false;
    }

    const data = {
      registration_no: this.pvNumber ,
    };
    this.spinner.show();

    // load Company data from the server
    this.general.checkCompanByRegNumberForAdminReg(data)
      .subscribe(
        req => {
          if (req['status']) {

            if (this.adminCompanies.includes( req['company_id'] ) ){
              this.spinner.hide();
              return false;
            }

            this.adminCompanies.push(req['company_id']);
            console.log(this.adminCompanies);
            this.currentCompanyList.push({id: req['company_id'] , regNumber: this.pvNumber, name: req['company_name']} );
            this.pvNumber = '';
          } else {
            alert(req['message']);
          }
          this.spinner.hide();
        }
      );

  }


  removeAssignedCompany(companyId){
    let copyAdminCompanies = this.adminCompanies;
    this.adminCompanies = [];
    for ( var i = 0; i < copyAdminCompanies.length; i++){
      if ( copyAdminCompanies[i] === companyId ) {
       continue;
      }
      this.adminCompanies.push(copyAdminCompanies[i]);

    }

    let copyList = Object.assign({}, this.currentCompanyList);
    this.currentCompanyList = [];

    for (let j in copyList) {
         if (copyList[j].id === companyId ) {
            continue;
         }
         this.currentCompanyList.push(copyList[j]);

    }


  }

}

