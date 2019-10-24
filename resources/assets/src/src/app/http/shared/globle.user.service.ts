import { HelperService } from './helper.service';
import { AuthenticationService } from '../services/authentication.service';
import { DomSanitizer } from '@angular/platform-browser';
import { Injectable, Optional } from '@angular/core';

export class UserServiceConfig {
  id: string;
  key: string;
  title: string;
  first_name: string;
  last_name: string;
  other_name: string;
  nic: string;
  passport_no: string;
  passport_issued_country: string;
  telephone: string;
  mobile: string;
  email: string;
  dob: string;
  sex: string;
  is_srilankan: string;
  occupation: string;
  profile_pic: string;
  status: string;
  created_at: string;
  updated_at: string;
  address_id: string;
  address1: string;
  address2: string;
  city: string;
  district: string;
  province: string;
  gn_division: string;
  country: string;
  postcode: string;
  tender_user: string;
}
@Injectable({
  providedIn: 'root'
})
export class GlobleUserService {
  image: any = 'assets/images/avatar/user-avatar.png';
  private user: UserServiceConfig;
  constructor(public authentication: AuthenticationService, private helper: HelperService) {
    this.setUserData(); this.setAvater();
  }

  setUserData() {
    this.user = JSON.parse(localStorage.getItem('User'));
  }

  setAvater() {
    if (this.user !== null) {
      if (this.user.profile_pic !== null && this.user.profile_pic.replace(/\s/g, '').length > 0) {
        this.authentication.augetAvater(this.user.profile_pic)
          .subscribe(
            req => {
              this.image = this.helper.view(req);
            });
      }
    }
  }

  get getPeopleId(): string {
    return this.user.id;
  }

  get getTitle(): string {
    return this.user.key;
  }

  get getFirstName(): string {
    return this.user.first_name;
  }

  get getLastName(): string {
    return this.user.last_name;
  }

  get getOtherName(): string {
    return this.user.other_name;
  }

  get getUserName(): string {
    return this.user.title + this.user.first_name + ' ' + this.user.last_name;
  }

  get getNIC(): string {
    return this.user.nic;
  }

  get getEmail(): string {
    return this.user.email;
  }

  get getMobile(): string {
    return this.user.mobile;
  }

  get getTelephone(): string {
    return this.user.telephone;
  }

  get getOccupation(): string {
    return this.user.occupation;
  }

  get getAvater(): any {
    return this.image;
  }

  get getDateTime(): string {
    return this.user.created_at;
  }

  get getAddressId(): string {
    return this.user.address_id;
  }

  get getAddress(): any {
    var address = '';
    if (this.user.address1 !== null && this.user.address1.replace(/\s/g, '').length > 0) {
      address = address + this.user.address1 + '';
    }
    if (this.user.address2 !== null && this.user.address2.replace(/\s/g, '').length > 0) {
      address = address + ',' + this.user.address2 + '';
    }
    if (this.user.city !== null && this.user.city.replace(/\s/g, '').length > 0) {
      address = address + ',' + this.user.city + '';
    }
    if (this.user.district !== null && this.user.district.replace(/\s/g, '').length > 0) {
      address = address + ',' + this.user.district + '';
    }
    if (this.user.gn_division !== null && this.user.gn_division.replace(/\s/g, '').length > 0) {
      address = address + ',' + this.user.gn_division + '';
    }
    if (this.user.country !== null && this.user.country.replace(/\s/g, '').length > 0) {
      address = address + ',' + this.user.country + '';
    }
    if (this.user.province !== null && this.user.province.replace(/\s/g, '').length > 0) {
      address = address + ',' + this.user.province + '';
    }
    return address;
  }

  get getAddress01(): string {
    return this.user.address1;
  }

  get getAddress02(): string {
    return this.user.address2;
  }

  get getCity(): string {
    return this.user.city;
  }

  get getDistrict(): string {
    return this.user.district;
  }

  get getProvince(): string {
    return this.user.province;
  }

  get getGNDivision(): string {
    return this.user.gn_division;
  }

  get getCountry(): string {
    return this.user.country;
  }

  get getPostCode(): string {
    return this.user.postcode;
  }

  get getTenderUser(): boolean {
    return this.user.tender_user === 'yes' ? true : false;
  }
  get isUser(): boolean {
    return this.user !== null ? true : false;
  }
}
