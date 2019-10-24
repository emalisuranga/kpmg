import { Injectable } from '@angular/core';

@Injectable({
  providedIn: 'root'
})
export class AuditorDataService {

  audId: number;
  firmId: number;
  nic: string;
  passport: string;

  constructor() { }

  // for continue upload process after some reasons...
  setAudId(audId: number) {
    this.audId = audId;
  }

  get getAudId() {
    return this.audId;
  }

  setFirmId(firmId: number) {
    this.firmId = firmId;
  }

  get getFirmId() {
    return this.firmId;
  }

  setNic(nic: string) {
    this.nic = nic;
  }

  get getNic() {
    return this.nic;
  }

  setPassport(passport: string) {
    this.passport = passport;
  }

  get getPassport() {
    return this.passport;
  }


}
