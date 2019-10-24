import { Directive } from '@angular/core';
import { AsyncValidator, AbstractControl, ValidationErrors, NG_ASYNC_VALIDATORS } from '@angular/forms';
import { Observable } from 'rxjs';
import { map } from 'rxjs/operators';
import { AuthenticationService } from '../../http/services/authentication.service';

@Directive({
  // tslint:disable-next-line:directive-selector
  selector: '[uniquePassword]',
  providers: [{provide: NG_ASYNC_VALIDATORS, useExisting: UniquePasswordDirective, multi: true}]
})

export class UniquePasswordDirective implements AsyncValidator {

  constructor(private auth: AuthenticationService) { }

  validate(c: AbstractControl): Promise<ValidationErrors> | Observable<ValidationErrors> {
    return this.auth.auIsChackSamePassword(c.value).pipe(
      map(response => {
        return response !== 'false' ? { 'uniquePassword': true } : null;
      })
    );
  }
}
