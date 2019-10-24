import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { NameValidityExtensionComponent } from './name-validity-extension.component';

describe('NameValidityExtensionComponent', () => {
  let component: NameValidityExtensionComponent;
  let fixture: ComponentFixture<NameValidityExtensionComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ NameValidityExtensionComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(NameValidityExtensionComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
