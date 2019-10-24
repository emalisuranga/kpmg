import { Injectable } from '@angular/core';

@Injectable({
  providedIn: 'root'
})
export class SecretaryDataService {
  secId: number;
  firmId: number;
  nic: string;
  constructor() { }

  // for continue upload process after some reasons...
  setSecId(secId: number) {
    this.secId = secId;
  }

  get getSecId() {
    return this.secId;
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

}
